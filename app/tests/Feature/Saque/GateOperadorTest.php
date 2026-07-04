<?php

namespace Tests\Feature\Saque;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

/**
 * Autorização por papéis (ADR-009). O papel `operador` concede o acesso ao backoffice de
 * saque (STORY-017) via Gate `operar-saques`. Colaborador comum não tem o papel.
 */
class GateOperadorTest extends TestCase
{
    use RefreshDatabase;

    private function operador(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['nome' => 'operador']));

        return $user;
    }

    public function test_has_role_reflete_o_papel_atribuido(): void
    {
        $operador = $this->operador();
        $comum = User::factory()->create();

        $this->assertTrue($operador->hasRole('operador'));
        $this->assertFalse($comum->hasRole('operador'));
    }

    public function test_operador_pode_operar_saques(): void
    {
        $this->assertTrue(Gate::forUser($this->operador())->allows('operar-saques'));
    }

    public function test_colaborador_comum_nao_pode_operar_saques(): void
    {
        $this->assertFalse(Gate::forUser(User::factory()->create())->allows('operar-saques'));
    }
}
