<?php

namespace App\Http\Controllers;

use App\Models\Liability;
use Illuminate\Http\Request;

class LiabilityController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'type' => 'nullable|string',
        ]);

        Liability::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return redirect()->back();
    }

    public function destroy(Liability $liability)
    {
        if ($liability->user_id !== auth()->id()) {
            abort(403);
        }

        $liability->delete();

        return redirect()->back();
    }

    public function update(Liability $liability)
    {
        if ($liability->user_id !== auth()->id()) {
            abort(403);
        }

        $liability->update([
            'checked' => ! $liability->checked,
        ]);

        return redirect()->back();
    }
}
