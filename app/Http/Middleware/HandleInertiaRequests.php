<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'appUrl' => config('app.url'),
            'auth' => [
                'user' => $request->user(),
                'security' => $request->user() ? [
                    'isVerified' => $request->user()->hasVerifiedEmail(),
                    'isOnboarded' => $request->user()->is_onboarded,
                    'mustResetPassword' => $request->user()->must_reset_password,
                    'hasTransactionPin' => $request->user()->pin !== null,
                    'hasTwoFactorAuthentication' => $request->user()->two_factor_confirmed_at !== null,
                ] : null,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
        ];
    }
}
