<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_user_can_complete_onboarding_once(): void
    {
        $user = User::factory()->create(['is_onboarded' => false]);

        $response = $this->actingAs($user)->post(route('onboarding.store'), [
            'account_name' => 'Main Wallet',
            'initial_balance' => 1000,
            'currency' => 'PHP',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertTrue($user->refresh()->is_onboarded);
        $this->assertDatabaseHas('accounts', ['user_id' => $user->id, 'name' => 'Main Wallet']);

        $this->actingAs($user)->post(route('onboarding.store'), [
            'account_name' => 'Duplicate',
            'initial_balance' => 0,
            'currency' => 'USD',
        ]);

        $this->assertDatabaseCount('accounts', 1);
    }

    public function test_unverified_user_cannot_access_onboarding(): void
    {
        $user = User::factory()->unverified()->create(['is_onboarded' => false]);

        $this->actingAs($user)->get(route('onboarding.show'))
            ->assertRedirect(route('verification.notice'));
    }
}
