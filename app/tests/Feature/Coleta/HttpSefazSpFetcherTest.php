<?php

namespace Tests\Feature\Coleta;

use App\Domain\Coleta\ChaveAcesso;
use App\Domain\Coleta\Sefaz\HttpSefazSpFetcher;
use App\Domain\Coleta\Sefaz\SefazExtracaoException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Classificação de falha do fetcher real da SEFAZ-SP (ADR-002). Sem tocar o portal:
 * as respostas são fakeadas (`Http::fake`). Garante que cada situação vira o tipo certo
 * (transitória/negócio/estrutural) — o que dirige retry/alerta no Job.
 */
class HttpSefazSpFetcherTest extends TestCase
{
    private function chave(): ChaveAcesso
    {
        return ChaveAcesso::deEntrada('35260112345678000195650010001234561000000019');
    }

    private function esperarFalha(string $tipo): void
    {
        try {
            (new HttpSefazSpFetcher(2))->buscar($this->chave());
            $this->fail("Deveria lançar SefazExtracaoException do tipo $tipo.");
        } catch (SefazExtracaoException $e) {
            $this->assertSame($tipo, $e->tipo);
        }
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

    public function test_cupom_inexistente_e_falha_de_negocio(): void
    {
        Http::fake(['*' => Http::response('<html>NFC-e não encontrada</html>', 200)]);
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
}
