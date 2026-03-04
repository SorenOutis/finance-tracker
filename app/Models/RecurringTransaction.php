<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecurringTransaction extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'amount',
        'type',
        'category',
        'description',
        'frequency',
        'next_due_date',
        'is_active',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'next_due_date' => 'date',
        'is_active' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
