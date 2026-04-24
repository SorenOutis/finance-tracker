<?php

namespace App\Http\Controllers;

use App\Models\FinancialGoal;
use Illuminate\Http\Request;

class FinancialGoalController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'target_amount' => 'required|numeric|min:1',
            'current_amount' => 'nullable|numeric|min:0',
            'deadline' => 'nullable|date',
            'color' => 'nullable|string|max:20',
        ]);

        FinancialGoal::create([
            'user_id' => auth()->id(),
            'name' => $request->name,
            'target_amount' => $request->target_amount,
            'current_amount' => $request->current_amount ?? 0,
            'deadline' => $request->deadline,
            'color' => $request->color ?? '#14b8a6',
        ]);

        return back();
    }

    public function update(Request $request, FinancialGoal $financial_goal)
    {
        $request->validate([
            'current_amount' => 'nullable|numeric|min:0',
            'name' => 'nullable|string|max:255',
            'target_amount' => 'nullable|numeric|min:1',
        ]);

        $financial_goal->update($request->only(['current_amount', 'name', 'target_amount']));

        return back();
    }

    public function destroy(FinancialGoal $financial_goal)
    {
        $financial_goal->delete();

        return back();
    }
}
