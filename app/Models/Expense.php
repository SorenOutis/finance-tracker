<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Expense extends Model
{
    protected $fillable = ['user_id', 'account_id', 'amount', 'date', 'category', 'description', 'receipt_photo_path'];

    protected $casts = [
        'date' => 'datetime',
        'amount' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the full URL to the receipt photo, or null if none exists.
     */
    public function getReceiptUrlAttribute(): ?string
    {
        if (!$this->receipt_photo_path) {
            return null;
        }

        return Storage::disk('public')->url($this->receipt_photo_path);
    }

    /**
     * Delete the receipt photo when the expense is deleted.
     */
    protected static function booted(): void
    {
        static::deleting(function (Expense $expense) {
            if ($expense->receipt_photo_path) {
                Storage::disk('public')->delete($expense->receipt_photo_path);
            }
        });
    }
}
