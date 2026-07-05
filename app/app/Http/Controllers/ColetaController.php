<?php

namespace App\Http\Controllers;

use App\Domain\Coleta\IngestaoCupomService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Captura do cupom (STORY-009) — tela mobile de scan/colar o QR da NFC-e e handoff
 * para a ingestão (ADR-001). NÃO reimplementa validação/dedup: delega ao
 * IngestaoCupomService::capturar(). Validação SEFAZ/persistência canônica é STORY-010.
 */
class ColetaController extends Controller
{
    /**
     * Microcopy dos erros de captura (rejeição) — espelha o spec de tela
     * `design/screens/STORY-009-captura-qr-confirmacao.md` §5. Ancorados no campo (CA-4).
     *
     * `cupom_expirado` (STORY-035) usa o MESMO mecanismo de rejeição ancorada no campo — não é
     * banner global. O prazo (N) vem de `config('coleta.janela_dias')`, então a mensagem é
     * montada em runtime por `microcopyRejeicao()`.
     */
    public static function microcopyRejeicao(?string $motivo): string
    {
        return match ($motivo) {
            'chave_malformada' => 'Esse código não parece um QR de NFC-e. Confira e tente de novo.',
            'fora_de_escopo_uf' => 'Por enquanto o Quantah só coleta notas de São Paulo.',
            'modelo_invalido' => 'Esse documento não é uma NFC-e.',
            'cupom_expirado' => 'Esse cupom passou do prazo de '.(int) config('coleta.janela_dias', 7)
                .' dias para valer. Envie notas mais recentes.',
            default => 'Não foi possível ler esse cupom.',
        };
    }

    public function create(Request $request): Response
    {
        // Link compartilhado abre o app já com a entrada preenchida (share/deep link — CA-2).
        return Inertia::render('Coleta/Captura', [
            'entradaInicial' => $request->query('p') ?? $request->query('url'),
        ]);
    }

    public function store(Request $request, IngestaoCupomService $ingestao): RedirectResponse
    {
        $dados = $request->validate([
            'entrada' => ['required', 'string', 'max:2048'],
            'origem' => ['nullable', 'in:scan,compartilhado'],
        ]);

        // O Colaborador logado é atribuído ao cupom novo (STORY-015) — dono do cashback.
        $resultado = $ingestao->capturar(
            $dados['entrada'],
            $dados['origem'] ?? 'scan',
            $request->user()->id,
        );

        if ($resultado->foiRejeitado()) {
            // Erro ancorado no campo (CA-4), não global.
            throw ValidationException::withMessages([
                'entrada' => self::microcopyRejeicao($resultado->motivo),
            ]);
        }

        // CAPTURADO ou DUPLICADO → confirmação via flash (sem PII).
        return back()->with('coleta', $resultado->toArray());
    }
}
