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
            'password' => '123456',
            'password_confirmation' => '123456',
            'account_name' => 'Test Wallet',
            'initial_balance' => 1000,
            'currency' => 'PHP',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

        $this->assertDatabaseHas('accounts', [
            'name' => 'Test Wallet',
            'initial_balance' => 1000,
            'currency' => 'PHP',
        ]);
    }
}
