<?php

namespace Tests\Feature\Auth;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered()
    {
        $response = $this->get(route('register'));

        $response->assertOk();
    }

    public function test_new_users_can_register()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'F1nance!Vault#2026',
            'password_confirmation' => 'F1nance!Vault#2026',
            'terms' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('verification.notice', absolute: false));

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'terms_version' => '2026-01',
        ]);
        $this->assertDatabaseCount('accounts', 0);
    }

    public function test_registration_requires_terms_and_a_strong_password()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => '123456',
            'password_confirmation' => '123456',
        ]);

        $response->assertSessionHasErrors(['password', 'terms']);
        $this->assertDatabaseCount('users', 0);
    }
}
