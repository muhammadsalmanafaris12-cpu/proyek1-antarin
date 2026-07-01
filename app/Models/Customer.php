<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'name', 'phone', 'email', 'address', 'latitude', 'longitude',
        'cancel_count', 'order_count', 'is_flagged',
    ];

    protected $casts = [
        'is_flagged' => 'boolean',
        'latitude'   => 'decimal:7',
        'longitude'  => 'decimal:7',
    ];

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /** Account age in days */
    public function getAccountAgeDaysAttribute(): int
    {
        return (int) $this->created_at->diffInDays(now());
    }
}
