<?php

namespace App\Http\Plugins;

use App\Models\Event;
use App\Models\Reservation;

interface Plugin
{
    public function supports(Event $event): bool;
    public function get(Reservation $reservation): String;

}
