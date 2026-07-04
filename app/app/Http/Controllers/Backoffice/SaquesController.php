<?php

namespace App\Http\Controllers\Backoffice;

use App\Domain\Saque\Cpf;
use App\Domain\Saque\SaqueInvalidoException;
use App\Domain\Saque\SaqueService;
use App\Domain\Saque\TransicaoInvalidaException;
use App\Http\Controllers\Controller;
use App\Models\Saque;
use App\Support\Formato;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Backoffice de saques (STORY-017, ADR-005) — superfície interna atrás do Gate
 * `operar-saques` (ADR-009). O operador conduz a máquina de estados (assumir/aprovar/pagar/
 * rejeitar). CPF é **mascarado** na lista (minimização de PII, ADR-006); o valor canônico
 * completo aparece só no detalhe, onde a operação exige conferir a titularidade.
 */
class SaquesController extends Controller
{
    public function __construct(private readonly SaqueService $service) {}

    public function index(Request $request): Response
    {
        $query = Saque::query()->orderByDesc('created_at');
        if ($status = $request->query('status')) {
            $query->where('status', $status);
        }

        $saques = $query->get()->map(fn (Saque $s) => [
            'id' => $s->id,
            'valor_reais' => self::reais($s->valor_centavos),
            'cpf_mascarado' => Cpf::mascarar($s->cpf),
            'status' => $s->status,
            'solicitado_em' => Formato::dataHora($s->created_at),
        ]);

        return Inertia::render('Backoffice/Saques/Index', [
            'saques' => $saques,
            'filtro' => $request->query('status'),
        ]);
    }

    public function show(Saque $saque): Response
    {
        return Inertia::render('Backoffice/Saques/Detalhe', [
            'saque' => [
                'id' => $saque->id,
                'valor_reais' => self::reais($saque->valor_centavos),
                'cpf' => $saque->cpf,
                'chave_pix' => $saque->chave_pix,
                'status' => $saque->status,
                'comprovante' => $saque->comprovante,
                'solicitado_em' => Formato::dataHora($saque->created_at),
            ],
        ]);
    }

    public function assumir(Request $request, Saque $saque): RedirectResponse
    {
        return $this->executar($saque, fn () => $this->service->assumir($saque, $request->user()));
    }

    public function aprovar(Saque $saque): RedirectResponse
    {
        return $this->executar($saque, fn () => $this->service->aprovar($saque));
    }

    public function pagar(Request $request, Saque $saque): RedirectResponse
    {
        $data = $request->validate(
            ['comprovante' => ['required', 'string']],
            ['comprovante.required' => 'Informe o comprovante (e2e / id do PIX).']
        );

        return $this->executar($saque, fn () => $this->service->pagar($saque, $data['comprovante']));
    }

    public function rejeitar(Request $request, Saque $saque): RedirectResponse
    {
        return $this->executar($saque, fn () => $this->service->rejeitar($saque, $request->input('motivo')));
    }

    /** Executa a transição e trata erro de estado/regra como erro de tela (snackbar). */
    private function executar(Saque $saque, callable $acao): RedirectResponse
    {
        try {
            $acao();
        } catch (TransicaoInvalidaException|SaqueInvalidoException $e) {
            return back()->withErrors(['saque' => $e->getMessage()]);
        }

        return redirect()->route('backoffice.saques.show', $saque->id)->with('saque_ok', true);
    }

    private static function reais(int $centavos): string
    {
        return Formato::moeda($centavos);
    }
}
