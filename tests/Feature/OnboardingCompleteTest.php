<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingCompleteTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_complete_onboarding()
    {
        $response = $this->post(route('onboarding.complete'));

        $response->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_complete_onboarding()
    {
        $user = User::factory()->create([
            'is_onboarded' => false,
        ]);

        $response = $this
            ->actingAs($user)
            ->from(route('dashboard'))
            ->post(route('onboarding.complete'));

        $response->assertRedirect(route('dashboard'));

        $user->refresh();

        $this->assertTrue($user->is_onboarded);
    }

    public function test_is_onboarded_is_set_to_true_after_completion()
    {
        $user = User::factory()->create([
            'is_onboarded' => false,
        ]);

        $this->actingAs($user)->post(route('onboarding.complete'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_onboarded' => true,
        ]);
    }

    public function test_multiple_calls_are_idempotent()
    {
        $user = User::factory()->create([
            'is_onboarded' => false,
        ]);

        $this->actingAs($user)->post(route('onboarding.complete'));
        $this->actingAs($user)->post(route('onboarding.complete'));
        $this->actingAs($user)->post(route('onboarding.complete'));

        $user->refresh();

        $this->assertTrue($user->is_onboarded);
    }

    public function test_already_onboarded_user_can_still_call_endpoint()
    {
        $user = User::factory()->create([
            'is_onboarded' => true,
        ]);

        $response = $this
            ->actingAs($user)
            ->post(route('onboarding.complete'));

        $response->assertRedirect();

        $user->refresh();

        $this->assertTrue($user->is_onboarded);
    }
}
