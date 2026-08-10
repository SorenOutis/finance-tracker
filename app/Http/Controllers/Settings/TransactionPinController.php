<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\TransactionPinRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class TransactionPinController extends Controller
{
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/TransactionPin', [
            'hasPin' => $request->user()->pin !== null,
        ]);
    }

    public function update(TransactionPinRequest $request): RedirectResponse
    {
        $request->user()->update(['pin' => $request->validated('pin')]);

        return back()->with('status', 'transaction-pin-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validate(['current_password' => ['required', 'current_password']]);
        $request->user()->update(['pin' => null]);

        return back()->with('status', 'transaction-pin-removed');
    }
}
