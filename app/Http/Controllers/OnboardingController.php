<?php

namespace App\Http\Controllers;

use App\Http\Requests\CompleteOnboardingRequest;
use App\Models\Account;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OnboardingController extends Controller
{
    public function show(): Response|RedirectResponse
    {
        if (request()->user()->is_onboarded) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('auth/Onboarding', [
            'currencies' => [
                ['value' => 'PHP', 'label' => 'Philippine Peso (₱)'],
                ['value' => 'USD', 'label' => 'US Dollar ($)'],
                ['value' => 'EUR', 'label' => 'Euro (€)'],
                ['value' => 'JPY', 'label' => 'Japanese Yen (¥)'],
                ['value' => 'GBP', 'label' => 'British Pound (£)'],
            ],
        ]);
    }

    public function store(CompleteOnboardingRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $user = $request->user()->newQuery()->lockForUpdate()->findOrFail($request->user()->id);

            if ($user->is_onboarded) {
                return;
            }

            Account::create([
                'user_id' => $user->id,
                'name' => $request->validated('account_name'),
                'type' => 'cash',
                'initial_balance' => $request->validated('initial_balance'),
                'currency' => $request->validated('currency'),
            ]);

            $user->update(['is_onboarded' => true]);
        });

        return redirect()->route('dashboard');
    }

    public function complete(Request $request): RedirectResponse
    {
        $request->user()->update(['is_onboarded' => true]);

        return redirect()->route('dashboard');
    }
}
