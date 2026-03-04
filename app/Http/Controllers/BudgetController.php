<?php

namespace App\Http\Controllers;

use App\Models\Budget;
use Illuminate\Http\Request;

class BudgetController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'monthly_limit' => 'required|numeric|min:0',
        ]);

        Budget::updateOrCreate(
            ['user_id' => auth()->id()],
            ['monthly_limit' => $request->monthly_limit]
        );

        return back();
    }
}
