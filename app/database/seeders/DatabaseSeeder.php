<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Papéis de autorização (ADR-009 · RBAC). `operador` opera o backoffice de saque.
        $operador = Role::firstOrCreate(['nome' => Role::OPERADOR]);

        // Usuário de dev; também é operador para exercitar o backoffice em homologação.
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
        $user->roles()->syncWithoutDetaching($operador);
    }
}
