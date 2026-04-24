<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $fillable = [
        'user_id', 'name', 'type', 'color', 'initial_balance',
        'credit_limit', 'currency', 'last4', 'holder_name', 'is_archived',
    ];

    protected $casts = [
        'initial_balance' => 'decimal:2',
        'credit_limit' => 'decimal:2',
        'is_archived' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function incomes()
    {
        return $this->hasMany(Income::class);
    }

    public function expenses()
    {
        return $this->hasMany(Expense::class);
    }

    public function getBalanceAttribute(): float
    {
        $totalIn = $this->incomes()->sum('amount');
        $totalOut = $this->expenses()->sum('amount');

        return (float) $this->initial_balance + $totalIn - $totalOut;
    }
}
