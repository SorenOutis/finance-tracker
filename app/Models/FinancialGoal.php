<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class FinancialGoal extends Model
{
    protected $fillable = ['user_id', 'name', 'target_amount', 'current_amount', 'deadline', 'color'];

    protected $casts = [
        'target_amount' => 'decimal:2',
        'current_amount' => 'decimal:2',
        'deadline' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
