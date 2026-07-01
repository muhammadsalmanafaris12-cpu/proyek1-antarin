<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    protected $fillable = [
        'order_code','customer_id','restaurant_id','driver_id',
        'delivery_address','delivery_lat','delivery_lng',
        'subtotal','delivery_fee','total_amount',
        'status','is_suspicious','suspicion_score',
        'notes','taken_at','delivered_at',
    ];

    protected $casts = [
        'subtotal'        => 'decimal:2',
        'delivery_fee'    => 'decimal:2',
        'total_amount'    => 'decimal:2',
        'is_suspicious'   => 'boolean',
        'taken_at'        => 'datetime',
        'delivered_at'    => 'datetime',
        'delivery_lat'    => 'decimal:7',
        'delivery_lng'    => 'decimal:7',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function suspicion(): HasOne
    {
        return $this->hasOne(OrderSuspicion::class);
    }

    public function getSuspicionLevelColorAttribute(): string
    {
        return match($this->suspicion?->level) {
            'high'   => 'red',
            'medium' => 'yellow',
            'low'    => 'blue',
            default  => 'green',
        };
    }

    public static function generateCode(): string
    {
        return 'ORD-' . strtoupper(substr(uniqid(), -6));
    }
}
