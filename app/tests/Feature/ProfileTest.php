<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    /** STORY-036 CA-2 — a exclusão de conta foi removida: a rota não executa e a conta persiste. */
    public function test_account_deletion_route_is_removed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', ['password' => 'password']);

        // Rota removida → método não permitido nesta URI (GET/PATCH ainda existem).
        $response->assertStatus(405);
        $this->assertNotNull($user->fresh(), 'a conta não pode ter sido excluída');
        $this->assertAuthenticated();
    }

    /** STORY-036 CA-2 — a rota nomeada `profile.destroy` não existe mais no roteador. */
    public function test_profile_destroy_route_name_does_not_exist(): void
    {
        $this->assertFalse(
            app('router')->getRoutes()->hasNamedRoute('profile.destroy'),
            'a rota profile.destroy deveria ter sido removida'
        );
    }
}
