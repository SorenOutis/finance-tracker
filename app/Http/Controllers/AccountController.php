<?php

namespace App\Http\Controllers;

use App\Models\Account;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:gcash,maya,paypal,bank,cash,credit_card,crypto,other',
            'color' => 'nullable|string|max:32',
            'initial_balance' => 'nullable|numeric',
            'credit_limit' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
            'last4' => 'nullable|string|max:8',
            'holder_name' => 'nullable|string|max:255',
        ]);

        Account::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Account $account)
    {
        if ($account->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|in:gcash,maya,paypal,bank,cash,credit_card,crypto,other',
            'color' => 'nullable|string|max:32',
            'initial_balance' => 'nullable|numeric',
            'credit_limit' => 'nullable|numeric',
            'currency' => 'nullable|string|max:8',
            'last4' => 'nullable|string|max:8',
            'holder_name' => 'nullable|string|max:255',
            'is_archived' => 'nullable|boolean',
        ]);

        $account->update($validated);

        return redirect()->back();
    }

    public function destroy(Account $account)
    {
        if ($account->user_id !== auth()->id()) {
            abort(403);
        }

        $account->delete();

        return redirect()->back();
    }
}
