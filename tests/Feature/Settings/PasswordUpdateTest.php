<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordUpdateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_update_page_is_displayed()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get(route('user-password.edit'));

        $response->assertOk();
    }

    public function test_password_can_be_updated()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('user-password.edit'))
            ->put(route('user-password.update'), [
                'current_password' => 'F1nance!Vault#2026',
                'password' => 'NewSecurePassword2@',
                'password_confirmation' => 'NewSecurePassword2@',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect(route('user-password.edit'));

        $this->assertTrue(Hash::check('NewSecurePassword2@', $user->refresh()->password));
    }

    public function test_correct_password_must_be_provided_to_update_password()
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from(route('user-password.edit'))
            ->put(route('user-password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'NewSecurePassword2@',
                'password_confirmation' => 'NewSecurePassword2@',
            ]);

        $response
            ->assertSessionHasErrors('current_password')
            ->assertRedirect(route('user-password.edit'));
    }
}
