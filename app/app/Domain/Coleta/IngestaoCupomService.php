<?php

namespace App\Domain\Coleta;

use App\Domain\Coleta\Events\CupomValidado;
use App\Domain\Coleta\Sefaz\CupomExtraido;
use App\Domain\Coleta\Sefaz\SefazAdapter;
use App\Domain\Coleta\Sefaz\SefazExtracaoException;
use App\Jobs\ExtrairCupomJob;
use App\Models\ColetaEvento;
use App\Models\Cupom;
use App\Models\CupomAtribuicao;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Fronteira de ingestão do cupom (ADR-001) — a única porta que a camada web conhece.
 *
 * Dois caminhos, mesma regra de negócio (ADR-003):
 *  - `capturar()` (STORY-009/010, produção): valida a chave → dedup idempotente →
 *    persiste `pendente` → **despacha o ExtrairCupomJob** (extração assíncrona na fila
 *    Postgres, ADR-002). A fronteira responde em segundos; a validação SEFAZ roda depois.
 *  - `ingerir()` (síncrono): faz o mesmo e **extrai na hora** — usado pelo comando de CLI
 *    e pelos testes de núcleo. Compartilha `processarExtracao()` com o Job.
 *
 * A extração descarta CPF (ADR-006) e classifica falha (transitória/estrutural/negócio).
 */
final class IngestaoCupomService
{
    /** @var array<string, SefazAdapter> adaptador por UF (só SP no MVP) */
    private array $adaptadores = [];

    public function __construct(SefazAdapter ...$adaptadores)
    {
        foreach ($adaptadores as $adaptador) {
            $this->adaptadores[$adaptador->uf()] = $adaptador;
        }
    }

    /**
     * Captura + handoff assíncrono (produção): persiste `pendente` e enfileira a extração.
     */
    public function capturar(string $entrada, string $origem = 'scan', ?int $userId = null): ResultadoIngestao
    {
        $parse = $this->validarChave($entrada);
        if ($parse instanceof ResultadoIngestao) {
            return $this->registrar($parse); // rejeitado na porta
        }

        // O Colaborador logado (coleta autenticada, STORY-015) é atribuído ao cupom NOVO,
        // para receber o cashback quando ele validar. Dedup (ADR-003): só o 1º coletor.
        [$cupom, $novo] = $this->persistirPendente($parse, $origem, $entrada, $userId);

        // Enfileira a extração para cupom novo ou em falha (reprocessável) — ADR-002.
        if ($novo || $cupom->status === Cupom::STATUS_FALHA) {
            ExtrairCupomJob::dispatch($cupom->id);
        }

        return $this->registrar($novo
            ? ResultadoIngestao::capturado($cupom)
            : ResultadoIngestao::duplicado($cupom));
    }

    /**
     * Ingestão síncrona (CLI/testes): persiste e extrai na hora, sem passar pela fila.
     */
    public function ingerir(string $entrada, string $origem = 'scan'): ResultadoIngestao
    {
        $parse = $this->validarChave($entrada);
        if ($parse instanceof ResultadoIngestao) {
            return $this->registrar($parse);
        }

        [$cupom, $novo] = $this->persistirPendente($parse, $origem, $entrada);

        if (! $novo && $cupom->status !== Cupom::STATUS_FALHA) {
            return $this->registrar(ResultadoIngestao::duplicado($cupom));
        }

        return $this->registrar($this->processarExtracao($cupom));
    }

    /** Reprocessa um cupom em `falha` (ADR-002) — re-enfileira; idempotente, não duplica. */
    public function reprocessar(string $chaveAcesso): ResultadoIngestao
    {
        $cupom = Cupom::where('chave_acesso', $chaveAcesso)->firstOrFail();

        if ($cupom->status !== Cupom::STATUS_FALHA) {
            return ResultadoIngestao::duplicado($cupom);
        }

        ExtrairCupomJob::dispatch($cupom->id);

        return ResultadoIngestao::capturado($cupom);
    }

    /**
     * Extrai o cupom na SEFAZ, normaliza no modelo canônico e persiste — ou marca a falha
     * classificada. Chamado pelo ExtrairCupomJob (fila) e por `ingerir()` (síncrono).
     *
     * Retorna:
     *  - ACEITO           → validado e persistido;
     *  - FALHA_EXTRACAO   → transitória/estrutural (reprocessável; o Job decide retry);
     *  - REJEITADO        → negócio (cupom inexistente/cancelado na SEFAZ), sem retry.
     */
    public function processarExtracao(Cupom $cupom): ResultadoIngestao
    {
        $chave = ChaveAcesso::deEntrada($cupom->chave_acesso);

        $adaptador = $this->adaptadores[$chave->uf()] ?? null;
        if ($adaptador === null) {
            $cupom->update(['status' => Cupom::STATUS_FALHA, 'motivo_falha' => 'sem_adaptador_uf']);

            return ResultadoIngestao::falhaExtracao($cupom, SefazExtracaoException::ESTRUTURAL);
        }

        $cupom->update(['status' => Cupom::STATUS_EXTRAINDO, 'motivo_falha' => null]);

        try {
            $extraido = $adaptador->extrair($chave, $cupom->qr_conteudo);
        } catch (SefazExtracaoException $e) {
            if ($e->tipo === SefazExtracaoException::NEGOCIO) {
                $cupom->update(['status' => Cupom::STATUS_REJEITADO, 'motivo_falha' => $e->tipo]);

                return ResultadoIngestao::rejeitado($e->tipo, $cupom);
            }

            // transitória/estrutural = reprocessável (ADR-002).
            $cupom->update(['status' => Cupom::STATUS_FALHA, 'motivo_falha' => $e->tipo]);

            return ResultadoIngestao::falhaExtracao($cupom, $e->tipo);
        }

        // Janela de validade (STORY-035): `data_emissao` só é conhecida aqui (pós-extração),
        // então é o ponto onde o prazo é aferido. Cupom velho não vira dado nem cashback.
        if ($this->foraDaJanela($extraido->dataEmissao)) {
            $cupom->update(['status' => Cupom::STATUS_REJEITADO, 'motivo_falha' => 'cupom_expirado']);

            return ResultadoIngestao::rejeitado('cupom_expirado', $cupom);
        }

        $this->normalizarEpersistir($cupom, $extraido);

        return ResultadoIngestao::aceito($cupom->fresh('itens'));
    }

    /**
     * Cupom está fora do prazo de validade? (STORY-035 · IDR-013)
     *
     * Limite de `config('coleta.janela_dias')` (default 7), comparado em America/Sao_Paulo:
     * válido se a idade for ≤ N dias (limite inclusivo); estritamente mais velho → expirado.
     * Sem `data_emissao` não há como aferir o prazo → não rejeita por validade.
     */
    private function foraDaJanela(?string $dataEmissao): bool
    {
        if ($dataEmissao === null || $dataEmissao === '') {
            return false;
        }

        $janelaDias = (int) config('coleta.janela_dias', 7);
        $emissao = Carbon::parse($dataEmissao, 'America/Sao_Paulo');
        $limite = Carbon::now('America/Sao_Paulo')->subDays($janelaDias);

        return $emissao->lt($limite);
    }

    /**
     * Telemetria da coleta (STORY-012): registra UM evento por tentativa de envio, na
     * porta de entrada — inclusive os desfechos que não persistem cupom (rejeição de
     * parse, duplicata). É o denominador da taxa de sucesso da north-star. Sem PII
     * (ADR-006): só situação, motivo e o uuid técnico do cupom. Não é chamado por
     * `processarExtracao` (reprocessamento assíncrono não é um novo envio).
     */
    private function registrar(ResultadoIngestao $resultado): ResultadoIngestao
    {
        ColetaEvento::create([
            'situacao' => $resultado->situacao,
            'motivo' => $resultado->motivo,
            'cupom_id' => $resultado->cupom?->id,
        ]);

        return $resultado;
    }

    /**
     * Parse + validação estrutural e de escopo da chave (ADR-003). Retorna o VO válido,
     * ou um ResultadoIngestao REJEITADO quando a chave não serve (sem tocar o banco/portal).
     */
    private function validarChave(string $entrada): ChaveAcesso|ResultadoIngestao
    {
        try {
            $chave = ChaveAcesso::deEntrada($entrada);
        } catch (ChaveAcessoInvalidaException) {
            return ResultadoIngestao::rejeitado('chave_malformada');
        }

        if ($chave->uf() !== '35') {
            return ResultadoIngestao::rejeitado('fora_de_escopo_uf');
        }
        if ($chave->modelo() !== '65') {
            return ResultadoIngestao::rejeitado('modelo_invalido');
        }

        return $chave;
    }

    /**
     * Insere o cupom em `pendente` de forma idempotente (unique na chave, à prova de corrida).
     * Guarda o QR original (`$qrConteudo`) — necessário para a consulta assinada (STORY-010).
     *
     * @return array{0: Cupom, 1: bool} o cupom e se foi criado agora
     */
    private function persistirPendente(ChaveAcesso $chave, string $origem, string $qrConteudo, ?int $userId = null): array
    {
        // LGPD (ADR-006): o QR colado pode trazer CPF grudado como lixo de colagem, e
        // `qr_conteudo` é coluna da tabela canônica — escova ANTES de gravar (STORY-011
        // CA-1). O `p=chave|...` assinado é preservado; só o padrão de CPF sai.
        $qrConteudo = AnonimizadorCpf::limparTexto($qrConteudo);

        return DB::transaction(function () use ($chave, $origem, $qrConteudo, $userId) {
            $cupom = Cupom::firstOrCreate(
                ['chave_acesso' => $chave->valor()],
                [
                    'uf' => $chave->uf(),
                    'ano_mes' => $chave->anoMes(),
                    'cnpj_emitente' => $chave->cnpjEmitente(),
                    'modelo' => $chave->modelo(),
                    'status' => Cupom::STATUS_PENDENTE,
                    'origem' => $origem,
                    'qr_conteudo' => $qrConteudo,
                ],
            );

            // Atribuição do cupom NOVO ao Colaborador logado (STORY-015), na MESMA transação
            // da criação — na base de pagamento segregada (ADR-006). Só o 1º coletor (dedup,
            // ADR-003): reenvio de terceiro cai em `wasRecentlyCreated === false` e não reatribui.
            if ($cupom->wasRecentlyCreated && $userId !== null) {
                CupomAtribuicao::create(['cupom_id' => $cupom->id, 'user_id' => $userId]);
            }

            // Reenvio de um cupom em falha trazendo o QR assinado (antes só a chave) → atualiza.
            if (! $cupom->wasRecentlyCreated
                && $cupom->status === Cupom::STATUS_FALHA
                && str_contains($qrConteudo, '|')
                && ! str_contains((string) $cupom->qr_conteudo, '|')) {
                $cupom->update(['qr_conteudo' => $qrConteudo]);
            }

            return [$cupom, $cupom->wasRecentlyCreated];
        });
    }

    private function normalizarEpersistir(Cupom $cupom, CupomExtraido $extraido): void
    {
        DB::transaction(function () use ($cupom, $extraido) {
            $cupom->update([
                'numero' => $extraido->numero,
                'serie' => $extraido->serie,
                'data_emissao' => $extraido->dataEmissao,
                'valor_total' => $extraido->valorTotal,
                'status' => Cupom::STATUS_VALIDADO,
                'motivo_falha' => null,
                'extraido_em' => now(),
            ]);

            // Idempotência do reprocessamento: recria os itens do zero.
            $cupom->itens()->delete();
            foreach ($extraido->itens as $item) {
                $cupom->itens()->create([
                    'sequencia' => $item->sequencia,
                    'descricao' => $item->descricao,
                    'codigo_loja' => $item->codigoLoja,
                    'gtin' => $item->gtin,
                    'quantidade' => $item->quantidade,
                    'unidade' => $item->unidade,
                    'valor_unitario' => $item->valorUnitario,
                    'valor_total' => $item->valorTotal,
                ]);
            }
        });

        // Cupom válido-único-novo → avisa o contexto de Cashback (STORY-015). Disparado
        // APÓS o commit (o listener enfileirado só deve ver o cupom já persistido). O
        // crédito é idempotente, então o reprocessamento (que revalida) não duplica.
        CupomValidado::dispatch($cupom->id);
    }
}
