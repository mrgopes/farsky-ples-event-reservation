<?php

namespace App\Http\Plugins;

use App\Http\Controllers\Controller;
use App\Models\Event;
use App\Models\Reservation;
use Illuminate\Support\Facades\Log;

class TablesPlugin extends Controller implements Plugin
{
    public function supports(Event $event): bool
    {
        $eventConfigRaw = $event->additional_information;
        $eventConfig = json_decode($eventConfigRaw, true);

        return isset($eventConfig['tables']) && !empty($eventConfig['tables']);
    }

    public function get(Reservation $reservation): String
    {
        // {"tables": [{"name": "test", "seats": [1, 2, 3, 4, 5]}]}

        Log::debug("TablesPlugin get called for reservation ID: " . $reservation->id);

        $eventConfigRaw = $reservation->order->event->additional_information;
        $eventConfig = json_decode($eventConfigRaw, true);

        $seatNumber = $reservation->seat_number;

        if ($eventConfig['tables'] != null) {
            foreach ($eventConfig['tables'] as $table) {
                if (in_array($seatNumber, $table['seats'])) {
                    return 'Stôl ' . $table['name'];
                }
            }
        }

        return "";
    }
}
