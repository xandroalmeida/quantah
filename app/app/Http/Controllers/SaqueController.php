<?php

namespace App\Http\Controllers;

use App\Domain\Cashback\ExtratoCarteira;
use App\Domain\Saque\SaldoInsuficienteException;
use App\Domain\Saque\SaqueInvalidoException;
use App\Domain\Saque\SolicitarSaqueService;
use App\Http\Requests\SolicitarSaqueRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Solicitação de saque pelo Colaborador (STORY-017, ADR-005). Controller fino: mostra o
 * form com o saldo (read-model) e delega a reserva ao `SolicitarSaqueService`. Erros de
 * regra (saldo insuficiente, CPF/chave) voltam por campo. Saque assistido — não é instantâneo.
 */
class SaqueController extends Controller
{
    public function create(Request $request, ExtratoCarteira $extrato): Response
    {
        return Inertia::render('Saque/Solicitar', [
            'saldo' => $extrato->para($request->user())['saldo'],
        ]);
    }

    public function store(SolicitarSaqueRequest $request, SolicitarSaqueService $service): RedirectResponse
    {
        $valor = $request->validated('valor'); // reais normalizado
        $cpf = $request->validated('cpf');     // só dígitos; é também a chave PIX (tipo CPF)
        $centavos = (int) bcmul($valor, '100', 0);

        try {
            $service->solicitar($request->user(), $centavos, $cpf, $cpf);
        } catch (SaldoInsuficienteException|SaqueInvalidoException $e) {
            // Regra de dinheiro/estado só verificável no domínio (ex.: saldo sob lock).
            throw ValidationException::withMessages(['valor' => $e->getMessage()]);
        }

        return redirect('/carteira')->with('saque', ['status' => 'solicitado']);
    }
}
