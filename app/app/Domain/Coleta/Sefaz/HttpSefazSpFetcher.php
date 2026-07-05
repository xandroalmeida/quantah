<?php

namespace App\Domain\Coleta\Sefaz;

use App\Domain\Coleta\ChaveAcesso;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Fetcher real do portal público da NFC-e de SP (ADR-002).
 *
 * Consulta a página `qrcode?p=<QR assinado>` e parseia o DANFE ("Consulta Resumida
 * NFC-e"). Confirmado por sonda com QR real: **não há captcha** — o portal renderiza
 * o cupom server-side desde que o `p` venha completo e assinado (chave|versão|amb|
 * token|hash). Só a chave de 44 dígitos (ex.: digitada à mão) **não** basta.
 *
 * Classificação de falha (ADR-002): conexão/5xx/429 → transitória; QR inválido /
 * cupom inexistente → negócio; QR sem assinatura ou layout não reconhecido →
 * estrutural. Nunca inventa dado: sem a estrutura esperada, falha seguro.
 *
 * LGPD (ADR-006): o parser lê apenas itens/valores/emitente — **não** extrai o CPF do
 * consumidor; a defesa do AnonimizadorCpf ainda roda sobre o payload.
 */
final class HttpSefazSpFetcher implements SefazSpFetcher
{
    private const BASE = 'https://www.nfce.fazenda.sp.gov.br/qrcode';

    private const MARCADORES_BLOQUEIO = ['captcha', 'g-recaptcha', 'recaptcha', 'hcaptcha'];

    private const MARCADORES_NEGOCIO = ['qr code inválido', 'qr code invalido', 'não encontrada', 'nao encontrada', 'inexistente', 'cancelada'];

    public function __construct(
        private readonly int $timeoutSegundos = 15,
    ) {}

    public function buscar(ChaveAcesso $chave, ?string $qrConteudo = null): array
    {
        $p = $this->extrairParametroP($qrConteudo);
        if ($p === null) {
            throw SefazExtracaoException::estrutural(
                'QR sem assinatura: a consulta ao vivo de SP exige o QR completo (p=chave|versão|amb|token|hash), não só a chave.'
            );
        }

        try {
            $resposta = Http::timeout($this->timeoutSegundos)
                ->withHeaders(['User-Agent' => 'QuantahBot/1.0 (+coleta NFC-e)'])
                ->get(self::BASE, ['p' => $p]);
        } catch (ConnectionException $e) {
            throw SefazExtracaoException::transitoria('Portal SEFAZ-SP indisponível: '.$e->getMessage());
        }

        if ($resposta->serverError()) {
            throw SefazExtracaoException::transitoria('SEFAZ-SP retornou '.$resposta->status().'.');
        }
        if ($resposta->status() === 429) {
            throw SefazExtracaoException::transitoria('SEFAZ-SP aplicou rate limit (429).');
        }

        $html = $resposta->body();

        // Sucesso primeiro: se há itens no DANFE, é um cupom válido — não confundir com
        // marcadores que aparecem em <script> de uma página válida (ex.: "...Cancelada()").
        $itens = $this->parsearItens($html);
        if ($itens !== []) {
            return $this->montarPayload($html, $itens, $chave);
        }

        // Sem itens: classifica o porquê. Captcha vem em atributo (checa HTML cru);
        // recusa de negócio vem no texto visível (sem os <script>).
        $baixo = mb_strtolower($html);
        foreach (self::MARCADORES_BLOQUEIO as $marca) {
            if (str_contains($baixo, $marca)) {
                throw SefazExtracaoException::estrutural('Consulta SEFAZ-SP exigiu captcha/verificação.');
            }
        }
        $texto = mb_strtolower($this->texto(preg_replace('/<script.*?<\/script>/is', '', $html) ?? $html));
        foreach (self::MARCADORES_NEGOCIO as $marca) {
            if (str_contains($texto, $marca)) {
                throw SefazExtracaoException::negocio('Cupom inválido/inexistente/cancelado na SEFAZ-SP.');
            }
        }

        throw SefazExtracaoException::estrutural('Layout do portal SEFAZ-SP não reconhecido (sem itens no DANFE).');
    }

    /** Extrai o parâmetro `p` (QR assinado) de uma URL, ou aceita o próprio `p` cru. */
    private function extrairParametroP(?string $qrConteudo): ?string
    {
        if ($qrConteudo === null) {
            return null;
        }
        $qrConteudo = trim($qrConteudo);

        if (preg_match('/[?&]p=([^&\s]+)/i', $qrConteudo, $m)) {
            $qrConteudo = urldecode($m[1]);
        }

        // Precisa ter os campos assinados (chave|...|hash), não só os 44 dígitos.
        return str_contains($qrConteudo, '|') ? $qrConteudo : null;
    }

    /**
     * Extrai as linhas de item do DANFE ("Consulta Resumida NFC-e"). Vazio se não houver.
     *
     * @return list<array<string, mixed>>
     */
    private function parsearItens(string $html): array
    {
        preg_match_all('/<tr[^>]*id="Item[^"]*"[^>]*>(.*?)<\/tr>/is', $html, $linhas);

        $itens = [];
        foreach ($linhas[1] as $linha) {
            // O portal intercala nbsp ( ) que `\s` não casa — normaliza para espaço.
            $linha = str_replace(["\xc2\xa0", '&nbsp;', '&#160;'], ' ', $linha);
            $itens[] = [
                'descricao' => $this->texto($this->capturar('/<span class="txtTit">(.*?)<\/span>/is', $linha)),
                'codigo_loja' => $this->texto($this->capturar('/C.{1,2}digo:\s*([^\s<)]+)/is', $linha)) ?: null,
                'quantidade' => $this->numero($this->capturar('/class="Rqtd">.*?<\/strong>\s*([\d.,]+)/is', $linha)),
                'unidade' => $this->texto($this->capturar('/class="RUN">.*?<\/strong>\s*([^\s<]+)/is', $linha)) ?: 'UN',
                'valor_unitario' => $this->numero($this->capturar('/class="RvlUnit">.*?<\/strong>\s*([\d.,]+)/is', $linha)),
                'valor_total' => $this->numero($this->capturar('/class="valor">\s*([\d.,]+)\s*<\/span>/is', $linha)),
            ];
        }

        return $itens;
    }

    /**
     * Monta o payload canônico (ADR-001) a partir dos itens + totais/emissão do DANFE.
     *
     * @param  list<array<string, mixed>>  $itens
     * @return array<string, mixed>
     */
    private function montarPayload(string $html, array $itens, ChaveAcesso $chave): array
    {
        $valorTotal = $this->numero(
            $this->capturar('/Valor a pagar R\$:<\/label>\s*<span class="totalNumb[^"]*">([\d.,]+)/is', $html)
            ?: $this->capturar('/Valor total R\$:<\/label>\s*<span class="totalNumb[^"]*">([\d.,]+)/is', $html)
        );

        // Data de emissão: "…Emissão: </strong>01/07/2026 16:43:54" (fora dos <script>).
        $semScript = preg_replace('/<script.*?<\/script>/is', '', $html) ?? $html;
        $dataEmissao = $this->capturar('/Emiss.{0,40}?(\d{2}\/\d{2}\/\d{4}(?:\s+\d{2}:\d{2}:\d{2})?)/is', $semScript);

        // Nome do estabelecimento (STORY-034): razão social no topo do DANFE
        // (`<div id="u20" class="txtTopo">NOME</div>`). Ausente → null (UI degrada).
        $nomeEmitente = $this->texto($this->capturar('/class="txtTopo"[^>]*>(.*?)<\/div>/is', $html)) ?: null;

        // Endereço do estabelecimento: logradouro/nº/bairro, município e UF do emitente.
        [$enderecoEmitente, $municipioEmitente, $ufEmitente] = $this->parsearEndereco($html);

        return [
            'numero' => $chave->numero(),
            'serie' => $chave->serie(),
            'data_emissao' => $this->dataIso($dataEmissao),
            'valor_total' => $valorTotal !== '' ? $valorTotal : '0',
            'nome_emitente' => $nomeEmitente,
            'endereco_emitente' => $enderecoEmitente,
            'municipio_emitente' => $municipioEmitente,
            'uf_emitente' => $ufEmitente,
            'itens' => $itens,
        ];
    }

    /**
     * Extrai endereço/município/UF do emitente do bloco do topo do DANFE-SP.
     *
     * O emitente traz duas `<div class="text">`: a 1ª é o CNPJ, a 2ª é o endereço, com
     * os campos separados por vírgula ("logradouro, nº, complemento, bairro, município, UF"),
     * alguns podendo vir vazios. A UF é o último campo (sigla de 2 letras) e o município, o
     * penúltimo; o restante forma o endereço. Bloco ausente/incompleto → null (UI degrada).
     *
     * @return array{0: ?string, 1: ?string, 2: ?string} [endereço, município, UF]
     */
    private function parsearEndereco(string $html): array
    {
        $bruto = $this->texto(
            $this->capturar('/CNPJ:.*?<\/div>\s*<div[^>]*class="text"[^>]*>(.*?)<\/div>/is', $html)
        );
        if ($bruto === '') {
            return [null, null, null];
        }

        // Campos separados por vírgula; descarta os vazios (ex.: complemento em branco).
        $campos = array_values(array_filter(
            array_map('trim', explode(',', $bruto)),
            fn (string $c) => $c !== ''
        ));
        if ($campos === []) {
            return [null, null, null];
        }

        // UF = último campo, se for a sigla de 2 letras; município = o campo anterior.
        $uf = null;
        $municipio = null;
        if (preg_match('/^[A-Za-z]{2}$/', (string) end($campos))) {
            $uf = strtoupper((string) array_pop($campos));
            $municipio = $campos !== [] ? array_pop($campos) : null;
        }

        return [
            $campos !== [] ? implode(', ', $campos) : null,
            $municipio,
            $uf,
        ];
    }

    private function capturar(string $regex, string $texto): string
    {
        return preg_match($regex, $texto, $m) ? trim($m[1]) : '';
    }

    /** Remove tags e normaliza espaços. */
    private function texto(string $html): string
    {
        return trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($html))) ?? '');
    }

    /** Converte número BR ("1.234,56"/"0,825") para decimal ("1234.56"/"0.825"). */
    private function numero(string $br): string
    {
        if ($br === '') {
            return '0';
        }

        return str_replace(',', '.', str_replace('.', '', $br));
    }

    /** "01/07/2026 16:43:54" → "2026-07-01 16:43:54" (ISO, sem inventar fuso). */
    private function dataIso(string $br): ?string
    {
        if (! preg_match('#(\d{2})/(\d{2})/(\d{4})(?:\s+(\d{2}:\d{2}:\d{2}))?#', $br, $m)) {
            return null;
        }

        return "{$m[3]}-{$m[2]}-{$m[1]}".(isset($m[4]) ? " {$m[4]}" : ' 00:00:00');
    }
}
