<?php

namespace Tests\Feature\Coleta;

use App\Domain\Coleta\AnonimizadorCpf;
use App\Domain\Coleta\ChaveAcesso;
use App\Domain\Coleta\Sefaz\HttpSefazSpFetcher;
use App\Domain\Coleta\Sefaz\SefazExtracaoException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Fetcher real da SEFAZ-SP (ADR-002): classificação de falha + parser do DANFE.
 * Sem tocar o portal — as respostas são fakeadas (`Http::fake`), inclusive um HTML
 * real de cupom de SP (fixture `danfe-sp.html`, com CPF já mascarado — ADR-006).
 */
class HttpSefazSpFetcherTest extends TestCase
{
    // QR de teste (chave sintética) já assinado — passa da checagem de assinatura.
    private const QR = 'https://www.nfce.fazenda.sp.gov.br/qrcode?p=35260112345678000195650010001234561000000019|2|1|1|ABC123DEF';

    // QR real do cupom do fixture.
    private const QR_REAL = 'https://www.nfce.fazenda.sp.gov.br/qrcode?p=35260743259548002883652030000666061954634872|2|1|1|4FBDA25AD2D9AD27A38431225D8C0788404236FC';

    private function chave(): ChaveAcesso
    {
        return ChaveAcesso::deEntrada('35260112345678000195650010001234561000000019');
    }

    private function esperarFalha(string $tipo, ?string $qr = self::QR): void
    {
        try {
            (new HttpSefazSpFetcher(2))->buscar($this->chave(), $qr);
            $this->fail("Deveria lançar SefazExtracaoException do tipo $tipo.");
        } catch (SefazExtracaoException $e) {
            $this->assertSame($tipo, $e->tipo);
        }
    }

    public function test_qr_sem_assinatura_e_estrutural(): void
    {
        // Só a chave (sem os campos assinados) não permite consulta ao vivo.
        $this->esperarFalha(SefazExtracaoException::ESTRUTURAL, '35260112345678000195650010001234561000000019');
    }

    public function test_5xx_e_transitoria(): void
    {
        Http::fake(['*' => Http::response('erro interno', 503)]);
        $this->esperarFalha(SefazExtracaoException::TRANSITORIA);
    }

    public function test_429_rate_limit_e_transitoria(): void
    {
        Http::fake(['*' => Http::response('slow down', 429)]);
        $this->esperarFalha(SefazExtracaoException::TRANSITORIA);
    }

    public function test_erro_de_conexao_e_transitorio(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));
        $this->esperarFalha(SefazExtracaoException::TRANSITORIA);
    }

    public function test_qr_invalido_e_falha_de_negocio(): void
    {
        Http::fake(['*' => Http::response('<div>QR Code inválido</div>', 200)]);
        $this->esperarFalha(SefazExtracaoException::NEGOCIO);
    }

    public function test_captcha_e_falha_estrutural(): void
    {
        Http::fake(['*' => Http::response('<div class="g-recaptcha"></div>', 200)]);
        $this->esperarFalha(SefazExtracaoException::ESTRUTURAL);
    }

    public function test_layout_desconhecido_e_falha_estrutural(): void
    {
        Http::fake(['*' => Http::response('<html><body>pagina qualquer</body></html>', 200)]);
        $this->esperarFalha(SefazExtracaoException::ESTRUTURAL);
    }

    /** CA-1: parseia o DANFE real de SP no payload canônico (18 itens, total, unidades). */
    public function test_parseia_danfe_real_de_sp(): void
    {
        Http::fake(['*' => Http::response(file_get_contents(base_path('tests/fixtures/coleta/danfe-sp.html')), 200)]);

        $chaveReal = ChaveAcesso::deEntrada('35260743259548002883652030000666061954634872');
        $dados = (new HttpSefazSpFetcher(2))->buscar($chaveReal, self::QR_REAL);

        $this->assertCount(18, $dados['itens']);
        $this->assertSame('235.43', $dados['valor_total']);
        $this->assertSame(66606, $dados['numero']);
        $this->assertSame(203, $dados['serie']);
        $this->assertSame('2026-07-01 16:43:54', $dados['data_emissao']);

        // Primeiro item (unidade UN) e um item por peso (unidade KG).
        $primeiro = $dados['itens'][0];
        $this->assertSame('SALSICHA HOT DOG SADIA 500G', $primeiro['descricao']);
        $this->assertSame('39900', $primeiro['codigo_loja']);
        $this->assertSame('1', $primeiro['quantidade']);
        $this->assertSame('14.85', $primeiro['valor_unitario']);
        $this->assertSame('14.85', $primeiro['valor_total']);
        $this->assertSame('UN', $primeiro['unidade']);

        $porPeso = collect($dados['itens'])->firstWhere('unidade', 'KG');
        $this->assertNotNull($porPeso);
        $this->assertSame('0.506', $porPeso['quantidade']);
        $this->assertSame('69.9', $porPeso['valor_unitario']);
    }

    /** ADR-006: o parser NÃO extrai o CPF do consumidor para o payload. */
    public function test_payload_parseado_nao_contem_cpf(): void
    {
        Http::fake(['*' => Http::response(file_get_contents(base_path('tests/fixtures/coleta/danfe-sp.html')), 200)]);

        $chaveReal = ChaveAcesso::deEntrada('35260743259548002883652030000666061954634872');
        $dados = (new HttpSefazSpFetcher(2))->buscar($chaveReal, self::QR_REAL);

        $this->assertFalse(AnonimizadorCpf::contemCpf(json_encode($dados, JSON_UNESCAPED_UNICODE)));
    }
}
