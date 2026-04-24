<?php

namespace App\Http\Controllers;

use App\Models\RecurringTransaction;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RecurringTransactionController extends Controller
{
    public function index()
    {
        $bills = RecurringTransaction::where('user_id', auth()->id())
            ->orderBy('next_due_date', 'asc')
            ->get();

        return Inertia::render('Bills', [
            'bills' => $bills,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'frequency' => 'required|in:weekly,monthly,yearly',
            'next_due_date' => 'required|date',
        ]);

        RecurringTransaction::create([
            'user_id' => auth()->id(),
            ...$request->only(['name', 'amount', 'type', 'category', 'description', 'frequency', 'next_due_date']),
        ]);

        return back();
    }

    public function update(Request $request, RecurringTransaction $recurring_transaction)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'amount' => 'required|numeric|min:0.01',
            'type' => 'required|in:income,expense',
            'category' => 'nullable|string|max:255',
            'description' => 'nullable|string|max:500',
            'frequency' => 'required|in:weekly,monthly,yearly',
            'next_due_date' => 'required|date',
            'is_active' => 'required|boolean',
        ]);

        $recurring_transaction->update($request->only(['name', 'amount', 'type', 'category', 'description', 'frequency', 'next_due_date', 'is_active']));

        return back();
    }

    public function destroy(RecurringTransaction $recurring_transaction)
    {
        $recurring_transaction->delete();

        return back();
    }
}
