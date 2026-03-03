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

        $asset = Asset::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return response()->json($asset, 201);
    }

    public function index()
    {
        return Asset::where('user_id', auth()->id())->get();
    }
}
