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

        $liability = Liability::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return response()->json($liability, 201);
    }

    public function index()
    {
        return Liability::where('user_id', auth()->id())->get();
    }
}
