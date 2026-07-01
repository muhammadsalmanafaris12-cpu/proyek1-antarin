<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    protected $fillable = [
        'user_id','phone','vehicle_type','vehicle_plate','operational_area',
        'modal_saldo','is_online','is_verified',
        'latitude','longitude','photo',
        'total_orders','total_earnings','daily_earnings','rating',
        'last_reset_date','warned_at','suspend_reason',
        'appeal_reason','appeal_at',
    ];

    protected $casts = [
        'modal_saldo'     => 'decimal:2',
        'total_earnings'  => 'decimal:2',
        'daily_earnings'  => 'decimal:2',
        'is_online'       => 'boolean',
        'is_verified'     => 'boolean',
        'latitude'        => 'decimal:7',
        'longitude'       => 'decimal:7',
        'last_reset_date' => 'date',
        'warned_at'       => 'datetime',
        'appeal_at'       => 'datetime',
        'rating'          => 'decimal:1',
    ];

    /**
     * Apakah driver sudah punya cukup order untuk ditampilkan ratingnya.
     */
    public function hasRating(): bool
    {
        return !is_null($this->rating) && $this->total_orders >= 5;
    }

    protected static function booted()
    {
        static::retrieved(function ($driver) {
            $today = now()->toDateString();
            $lastReset = $driver->last_reset_date 
                ? ($driver->last_reset_date instanceof \Carbon\Carbon 
                    ? $driver->last_reset_date->toDateString() 
                    : substr($driver->last_reset_date, 0, 10))
                : null;

            if ($lastReset !== $today) {
                $driver->modal_saldo = 0;
                $driver->daily_earnings = 0;
                $driver->last_reset_date = $today;
                $driver->save();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function activeOrder()
    {
        return $this->orders()->whereIn('status', ['taken','processing'])->first();
    }
}
