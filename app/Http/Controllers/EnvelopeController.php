<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Envelope;
use Illuminate\Http\Request;
use Inertia\Inertia;

class EnvelopeController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        $envelopes = Envelope::where('user_id', $user->id)
            ->where('is_archived', false)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $accounts = Account::where('user_id', $user->id)
            ->where('is_archived', false)
            ->get(['id', 'name', 'type', 'color']);

        return Inertia::render('Envelopes', [
            'envelopes' => $envelopes,
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'current_amount' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:32',
            'icon' => 'nullable|string|max:64',
            'deadline' => 'nullable|date',
            'account_id' => 'nullable|exists:accounts,id',
        ]);

        $validated['user_id'] = auth()->id();
        $validated['current_amount'] ??= 0;

        Envelope::create($validated);

        return redirect()->back();
    }

    public function update(Request $request, Envelope $envelope)
    {
        if ($envelope->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:0',
            'current_amount' => 'nullable|numeric|min:0',
            'color' => 'nullable|string|max:32',
            'icon' => 'nullable|string|max:64',
            'deadline' => 'nullable|date',
            'account_id' => 'nullable|exists:accounts,id',
            'is_archived' => 'nullable|boolean',
        ]);

        $envelope->update($validated);

        return redirect()->back();
    }

    public function destroy(Envelope $envelope)
    {
        if ($envelope->user_id !== auth()->id()) {
            abort(403);
        }

        $envelope->delete();

        return redirect()->back();
    }

    public function allocate(Request $request, Envelope $envelope)
    {
        if ($envelope->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'from_account_id' => 'nullable|exists:accounts,id',
        ]);

        $amount = (float) $validated['amount'];

        if ($validated['from_account_id'] ?? false) {
            $account = Account::findOrFail($validated['from_account_id']);
            if ($account->user_id !== auth()->id()) {
                abort(403);
            }
        }

        $envelope->increment('current_amount', $amount);

        return redirect()->back();
    }

    public function withdraw(Request $request, Envelope $envelope)
    {
        if ($envelope->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
        ]);

        $amount = (float) $validated['amount'];

        if ($amount > $envelope->current_amount) {
            return back()->withErrors(['amount' => 'Insufficient envelope balance.']);
        }

        $envelope->decrement('current_amount', $amount);

        return redirect()->back();
    }
}
