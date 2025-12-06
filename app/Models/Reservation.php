<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'guest_name',
        'seat_number',
        'order_id',
        'qr_code',
    ];

    protected $casts = [
        'seat_number' => 'integer',
        'order_id' => 'integer',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function computeAdditionalInformation(): String
    {
        $event = $this->order->event;

        $additionalInfo = '';

        foreach ($event->getPlugins() as $pluginClass) {
            $plugin = new $pluginClass();
            if ($plugin->supports($event)) {
                $additionalInfo .= $plugin->get($this) . ' ';
            }
        }

        return trim($additionalInfo);
    }
}
