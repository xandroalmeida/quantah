<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed idempotente — seguro para rodar a cada deploy (migrate+seed em homologação).
     */
    public function run(): void
    {
        // Papéis de autorização (ADR-009 · RBAC). `operador` opera o backoffice de saque.
        $operador = Role::firstOrCreate(['nome' => Role::OPERADOR]);

        // Usuário de dev/homolog; também é operador para exercitar o backoffice.
        // firstOrCreate → idempotente (não duplica nem quebra em re-seed).
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ],
        );
        $user->roles()->syncWithoutDetaching($operador);
    }
}
