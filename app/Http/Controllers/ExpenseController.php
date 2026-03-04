<?php

namespace App\Http\Controllers;

use App\Models\Expense;
use Illuminate\Http\Request;

class ExpenseController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'category' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        Expense::create([
            'user_id' => auth()->id(),
            ...$validated,
        ]);

        return redirect()->back();
    }

    public function index()
    {
        return Expense::where('user_id', auth()->id())->get();
    }

    public function destroy(Expense $expense)
    {
        if ($expense->user_id !== auth()->id()) {
            abort(403);
        }
        
        $expense->delete();
        
        return redirect()->back();
    }
}
