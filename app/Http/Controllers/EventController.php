<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use Inertia\Inertia;

class EventController extends Controller
{
    public function show($url_slug)
    {
        $event = Event::where('url_slug', $url_slug)->firstOrFail();
        return Inertia::render('event/Show', [
            'event' => $event,
        ]);
    }
}
