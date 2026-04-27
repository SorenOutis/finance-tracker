<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Expense;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function page(Request $request)
    {
        $user = $request->user();

        $accountMap = Account::where('user_id', $user->id)
            ->get(['id', 'name', 'color', 'type'])
            ->keyBy('id');

        $expenses = Expense::where('user_id', $user->id)
            ->orderBy('date', 'desc')
            ->orderBy('id', 'desc')
            ->get()
            ->map(fn ($e) => [
                'id' => $e->id,
                'amount' => (float) $e->amount,
                'date' => $e->date->toDateString(),
                'category' => $e->category,
                'description' => $e->description,
                'account' => $accountMap[$e->account_id] ?? null,
            ]);

        $accounts = Account::where('user_id', $user->id)
            ->get(['id', 'name', 'type'])
            ->map(fn ($a) => ['id' => $a->id, 'name' => $a->name, 'type' => $a->type]);

        return Inertia::render('Expenses', [
            'expenses' => $expenses,
            'accounts' => $accounts,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0',
            'date' => 'required|date',
            'category' => 'nullable|string',
            'description' => 'nullable|string',
            'account_id' => 'nullable|exists:accounts,id',
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
