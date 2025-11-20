<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    protected $fillable = [
        'event_id',
        'status',
        'name',
        'email',
        'phone',
        'variable_symbol',
        'payment_note',
        'url_slug',
        'qr_code',
    ];

    protected $casts = [
        'event_id' => 'integer',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function tickets(): BelongsToMany
    {
        return $this->belongsToMany(Ticket::class)
            ->withPivot('amount')
            ->withTimestamps();
    }

    /**
     * Calculate the total price of all tickets in the order
     */
    public function getTotalPrice(): float
    {
        return $this->tickets->reduce(function ($sum, $ticket) {
            $amount = $ticket->pivot->amount ?? 1;
            return $sum + ($ticket->price * $amount);
        }, 0);
    }

    public function getRouteKeyName(): string
    {
        return 'url_slug';
    }
}
