<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirectResponse;

class GoogleAuthenticationController extends Controller
{
    public function redirectForLogin(Request $request): SymfonyRedirectResponse
    {
        $request->session()->put('google_auth_intent', 'login');

        return Socialite::driver('google')->scopes(['openid', 'email', 'profile'])->redirect();
    }

    public function redirectForRegistration(Request $request): SymfonyRedirectResponse|RedirectResponse
    {
        $request->validate(['terms' => ['accepted']]);
        $request->session()->put('google_auth_intent', 'register');
        $request->session()->put('google_terms_accepted', true);

        return Socialite::driver('google')->scopes(['openid', 'email', 'profile'])->redirect();
    }

    public function callback(Request $request): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $exception) {
            report($exception);

            return redirect()->route('login')->withErrors([
                'google' => 'Google sign-in could not be completed. Please try again.',
            ]);
        }

        $email = Str::lower((string) $googleUser->getEmail());
        $providerId = (string) $googleUser->getId();
        $emailIsVerified = filter_var(data_get($googleUser->user, 'email_verified', false), FILTER_VALIDATE_BOOL);

        if ($email === '' || ! $emailIsVerified) {
            return redirect()->route('login')->withErrors([
                'google' => 'Google must provide a verified email address.',
            ]);
        }

        $intent = $request->session()->pull('google_auth_intent', 'login');
        $termsAccepted = $request->session()->pull('google_terms_accepted', false);

        $user = DB::transaction(function () use ($email, $providerId, $googleUser, $intent, $termsAccepted): ?User {
            $socialAccount = SocialAccount::query()
                ->where('provider', 'google')
                ->where('provider_id', $providerId)
                ->first();

            if ($socialAccount) {
                return $socialAccount->user;
            }

            $user = User::query()->where('email', $email)->first();

            if (! $user && ($intent !== 'register' || ! $termsAccepted)) {
                return null;
            }

            if (! $user) {
                $user = User::forceCreate([
                    'name' => $googleUser->getName() ?: Str::before($email, '@'),
                    'email' => $email,
                    'email_verified_at' => now(),
                    'password' => Str::password(64),
                    'terms_accepted_at' => now(),
                    'terms_version' => config('auth.terms_version'),
                    'must_reset_password' => false,
                    'is_onboarded' => false,
                ]);
            } else {
                $user->forceFill([
                    'email_verified_at' => $user->email_verified_at ?: now(),
                    'must_reset_password' => false,
                ])->save();
            }

            $user->socialAccounts()->create([
                'provider' => 'google',
                'provider_id' => $providerId,
            ]);

            return $user;
        });

        if (! $user) {
            return redirect()->route('register')->withErrors([
                'google' => 'No account uses that Google email. Accept the terms and register first.',
            ]);
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate();

        return redirect()->intended($user->is_onboarded ? route('dashboard') : route('onboarding.show'));
    }
}
