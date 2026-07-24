<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_google_user_can_register(): void
    {
        Socialite::fake('google', $this->googleUser());

        $response = $this->withSession([
            'google_auth_intent' => 'register',
            'google_terms_accepted' => true,
        ])->get(route('google.callback'));

        $user = User::query()->where('email', 'google@example.com')->firstOrFail();

        $response->assertRedirect(route('onboarding.show'));
        $this->assertAuthenticatedAs($user);
        $this->assertNotNull($user->email_verified_at);
        $this->assertDatabaseHas('social_accounts', [
            'user_id' => $user->id,
            'provider' => 'google',
            'provider_id' => 'google-123',
        ]);
    }

    public function test_google_login_does_not_create_an_unconsented_account(): void
    {
        Socialite::fake('google', $this->googleUser());

        $this->withSession(['google_auth_intent' => 'login'])
            ->get(route('google.callback'))
            ->assertRedirect(route('register'));

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    private function googleUser(): SocialiteUser
    {
        return (new SocialiteUser)->map([
            'id' => 'google-123',
            'name' => 'Google User',
            'email' => 'google@example.com',
        ])->setRaw([
            'sub' => 'google-123',
            'name' => 'Google User',
            'email' => 'google@example.com',
            'email_verified' => true,
        ]);
    }
}
