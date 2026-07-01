<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderSuspicion extends Model
{
    protected $fillable = ['order_id','score','flags','level','reviewed','admin_notes'];

    protected $casts = [
        'flags'    => 'array',
        'reviewed' => 'boolean',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function getBadgeColorAttribute(): string
    {
        return match($this->level) {
            'high'   => 'bg-red-500',
            'medium' => 'bg-yellow-500',
            'low'    => 'bg-blue-500',
            default  => 'bg-gray-400',
        };
    }
}
