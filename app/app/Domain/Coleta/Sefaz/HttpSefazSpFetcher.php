<?php

namespace App\Domain\Coleta\Sefaz;

use App\Domain\Coleta\ChaveAcesso;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Fetcher real do portal público da NFC-e de SP (ADR-002).
 *
 * Faz o GET da página do QR (`.../qrcode?p=<chave>|...`) e classifica a resposta em
 * transitória / negócio / estrutural. A conversão HTML→campos é **best-effort** e
 * conservadora: se a página não tiver a estrutura esperada do DANFE — o caso mais
 * provável hoje, porque o portal exige **captcha** na consulta — trata como falha
 * **ESTRUTURAL** (não persiste lixo; o cupom vira `falha` reprocessável + alerta),
 * exatamente o comportamento desenhado na ADR-002.
 *
 * LIMITAÇÃO CONHECIDA (ver IDR-004): sem resolver o captcha, o caminho feliz de
 * extração ao vivo não fecha — o happy path é validado por fixture/fake (ADR-002:
 * "SEFAZ mockável em teste"). A extração ao vivo depende de resolver o captcha ou
 * migrar para a fonte oficial (decisão de produto/arquitetura).
 */
final class HttpSefazSpFetcher implements SefazSpFetcher
{
    private const BASE = 'https://www.nfce.fazenda.sp.gov.br/qrcode';

    /** Marcadores de que o portal barrou a consulta (captcha / verificação). */
    private const MARCADORES_BLOQUEIO = ['captcha', 'g-recaptcha', 'verificação de segurança', 'robot'];

    /** Marcadores de cupom inexistente/cancelado (falha de negócio). */
    private const MARCADORES_NEGOCIO = ['não encontrada', 'nao encontrada', 'inexistente', 'cancelada'];

    public function __construct(
        private readonly int $timeoutSegundos = 15,
    ) {}

    public function buscar(ChaveAcesso $chave): array
    {
        try {
            $resposta = Http::timeout($this->timeoutSegundos)
                ->withHeaders(['User-Agent' => 'QuantahBot/1.0 (+coleta NFC-e)'])
                ->get(self::BASE, ['p' => $chave->valor().'|2|1|1']);
        } catch (ConnectionException $e) {
            throw SefazExtracaoException::transitoria('Portal SEFAZ-SP indisponível: '.$e->getMessage());
        }

        if ($resposta->serverError()) {
            throw SefazExtracaoException::transitoria('SEFAZ-SP retornou '.$resposta->status().'.');
        }
        if ($resposta->status() === 429) {
            throw SefazExtracaoException::transitoria('SEFAZ-SP aplicou rate limit (429).');
        }

        $html = mb_strtolower($resposta->body());

        foreach (self::MARCADORES_NEGOCIO as $marca) {
            if (str_contains($html, $marca)) {
                throw SefazExtracaoException::negocio('Cupom não encontrado/cancelado na SEFAZ-SP.');
            }
        }

        foreach (self::MARCADORES_BLOQUEIO as $marca) {
            if (str_contains($html, $marca)) {
                throw SefazExtracaoException::estrutural(
                    'Consulta SEFAZ-SP exigiu captcha/verificação — extração automática bloqueada.'
                );
            }
        }

        // Sem a estrutura esperada do DANFE, não inventamos dados: falha estrutural.
        throw SefazExtracaoException::estrutural(
            'Layout do portal SEFAZ-SP não reconhecido (parser DANFE pendente de amostra real).'
        );
    }
}
