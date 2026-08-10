<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Contracts\Provider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class GoogleAuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_google_user_can_register(): void
    {
        $this->fakeGoogleUser($this->googleUser());

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
        $this->fakeGoogleUser($this->googleUser());

        $this->withSession(['google_auth_intent' => 'login'])
            ->get(route('google.callback'))
            ->assertRedirect(route('register'));

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    private function fakeGoogleUser(SocialiteUser $user): void
    {
        $provider = Mockery::mock(Provider::class, function (MockInterface $mock) use ($user): void {
            $mock->shouldReceive('user')->andReturn($user);
        });

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);
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
