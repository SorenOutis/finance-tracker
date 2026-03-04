<?php

namespace App\Http\Controllers;

use App\Models\Asset;
use Illuminate\Http\Request;

class AssetController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'amount' => 'required|numeric|min:0',
            'type' => 'nullable|string',
        ]);

        Asset::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return redirect()->back();
    }

    public function destroy(Asset $asset)
    {
        if ($asset->user_id !== auth()->id()) {
            abort(403);
        }

        $asset->delete();

        return redirect()->back();
    }

    public function update(Asset $asset)
    {
        if ($asset->user_id !== auth()->id()) {
            abort(403);
        }

        $asset->update([
            'checked' => !$asset->checked,
        ]);

        return redirect()->back();
    }
}
