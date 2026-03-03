<?php

namespace App\Http\Controllers;

use App\Models\Income;
use Illuminate\Http\Request;

class IncomeController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'category' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        Income::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return redirect()->back();
    }

    public function index()
    {
        return Income::where('user_id', auth()->id())->get();
    }
}
