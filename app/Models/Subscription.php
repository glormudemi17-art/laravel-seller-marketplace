<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    protected $fillable = [
        'seller_id',
        'plan_name',
        'amount',
        'period',
        'started_at',
        'expires_at',
        'renewed_at',
        'status',
        'payment_reference',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'started_at' => 'datetime',
        'expires_at' => 'datetime',
        'renewed_at' => 'datetime',
    ];

    public function seller(): BelongsTo
    {
        return $this->belongsTo(Seller::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' && $this->expires_at > now();
    }

    public function renew(): void
    {
        $this->update([
            'renewed_at' => now(),
            'status' => 'active',
            'expires_at' => $this->getNextExpirationDate(),
        ]);
    }

    private function getNextExpirationDate()
    {
        return match($this->period) {
            'monthly' => now()->addMonth(),
            'quarterly' => now()->addMonths(3),
            'yearly' => now()->addYear(),
        };
    }
}
