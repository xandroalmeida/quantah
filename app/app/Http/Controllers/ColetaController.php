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
     */
    private const MENSAGENS_REJEICAO = [
        'chave_malformada' => 'Esse código não parece um QR de NFC-e. Confira e tente de novo.',
        'fora_de_escopo_uf' => 'Por enquanto o Quantah só coleta notas de São Paulo.',
        'modelo_invalido' => 'Esse documento não é uma NFC-e.',
    ];

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

        $resultado = $ingestao->capturar($dados['entrada'], $dados['origem'] ?? 'scan');

        if ($resultado->foiRejeitado()) {
            // Erro ancorado no campo (CA-4), não global.
            throw ValidationException::withMessages([
                'entrada' => self::MENSAGENS_REJEICAO[$resultado->motivo]
                    ?? 'Não foi possível ler esse cupom.',
            ]);
        }

        // CAPTURADO ou DUPLICADO → confirmação via flash (sem PII).
        return back()->with('coleta', $resultado->toArray());
    }
}
