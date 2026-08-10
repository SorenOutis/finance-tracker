<?php

namespace Tests\Feature\Settings;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TransactionPinTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_set_and_remove_a_transaction_pin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->put(route('transaction-pin.update'), [
                'current_password' => 'F1nance!Vault#2026',
                'pin' => '482951',
                'pin_confirmation' => '482951',
            ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('482951', $user->refresh()->pin));

        $this->actingAs($user)
            ->withSession(['auth.password_confirmed_at' => time()])
            ->delete(route('transaction-pin.destroy'), [
                'current_password' => 'F1nance!Vault#2026',
            ])->assertSessionHasNoErrors();

        $this->assertNull($user->refresh()->pin);
    }

    public function test_transaction_pin_cannot_authenticate_the_user(): void
    {
        $user = User::factory()->create(['pin' => '482951']);

        $this->post(route('login.store'), [
            'email' => $user->email,
            'password' => '482951',
        ]);

        $this->assertGuest();
    }
}
