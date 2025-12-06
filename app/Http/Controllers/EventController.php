<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Location;
use Inertia\Inertia;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    /**
     * Show the form for creating a new event.
     */
    public function create()
    {
        $locations = Location::select('id', 'address', 'places_total')->get();

        return Inertia::render('EventCreate', [
            'locations' => $locations,
        ]);
    }

    /**
     * Store a newly created event in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url_slug' => 'required|string|max:255|unique:events,url_slug',
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'registration_start' => 'required|date',
            'registration_end' => 'required|date|after:registration_start',
            'seats_total' => 'nullable|integer|min:1',
            'location_id' => 'required|exists:locations,id',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'bank_account' => 'required|string|max:255',
            'multiple_reservations_per_ticket' => 'boolean',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
        ]);

        // If seats_total is not provided, infer it from the location
        if (empty($validated['seats_total'])) {
            $location = Location::findOrFail($validated['location_id']);
            $validated['seats_total'] = $location->places_total;
        }

        // Handle background image upload
        $backgroundImagePath = null;
        if ($request->hasFile('background_image')) {
            $backgroundImagePath = $request->file('background_image')->store('events/backgrounds', 'public');
        }

        $event = Event::create([
            'user_id' => $request->user()->id,
            'title' => $validated['title'],
            'url_slug' => $validated['url_slug'],
            'description' => $validated['description'] ?? null,
            'start_time' => $validated['start_time'],
            'registration_start' => $validated['registration_start'],
            'registration_end' => $validated['registration_end'],
            'seats_total' => $validated['seats_total'],
            'location_id' => $validated['location_id'],
            'contact_name' => $validated['contact_name'],
            'contact_email' => $validated['contact_email'],
            'contact_phone' => $validated['contact_phone'] ?? null,
            'bank_account' => $validated['bank_account'],
            'multiple_reservations_per_ticket' => $validated['multiple_reservations_per_ticket'] ?? false,
            'background_image_path' => $backgroundImagePath,
        ]);


        return redirect()->route('event.show', $event->url_slug)
            ->with('success', 'Podujatie bolo úspešne vytvorené!');
    }

    /**
     * Show the event management page with tickets and orders.
     */
    public function manage(Request $request, $url_slug)
    {
        $event = Event::where('url_slug', $url_slug)
            ->with([
                'location',
                'tickets',
                'orders.tickets',
                'orders.reservations',
                'users' // Load collaborators
            ])
            ->firstOrFail();

        // Check if user has access to manage this event
        $user = $request->user();
        $hasAccess = $event->user_id === $user->id ||
                     $event->users()->where('user_id', $user->id)->exists();

        if (!$hasAccess) {
            abort(403, 'Nemáte oprávnenie na správu tohto podujatia.');
        }

        // Determine user role
        $userRole = 'owner';
        if ($event->user_id !== $user->id) {
            $pivotRole = $event->users()->where('user_id', $user->id)->first();
            $userRole = $pivotRole ? $pivotRole->pivot->role : 'staff';
        }

        $eventData = $event->toArray();
        $eventData['user_role'] = $userRole;
        $eventData['reserved_seats'] = $event->computeReservedSeats();

        // Get all users for the collaborator selection (only if owner)
        $allUsers = [];
        if ($userRole === 'owner') {
            $allUsers = \App\Models\User::select('id', 'name', 'email')
                ->where('id', '!=', $event->user_id)
                ->orderBy('name')
                ->get();
        }

        return Inertia::render('EventManage', [
            'event' => $eventData,
            'allUsers' => $allUsers,
        ]);
    }

    /**
     * Show the form for editing an event.
     */
    public function edit(Request $request, $url_slug)
    {
        $event = Event::where('url_slug', $url_slug)->firstOrFail();

        // Check if user has access to edit this event
        $user = $request->user();
        $hasAccess = $event->user_id === $user->id ||
                     $event->users()->where('user_id', $user->id)->whereIn('role', ['owner', 'manager'])->exists();

        if (!$hasAccess) {
            abort(403, 'Nemáte oprávnenie na úpravu tohto podujatia.');
        }

        $locations = Location::select('id', 'address', 'places_total')->get();

        return Inertia::render('EventEdit', [
            'event' => $event,
            'locations' => $locations,
        ]);
    }

    /**
     * Update the event in storage.
     */
    public function update(Request $request, $url_slug)
    {
        $event = Event::where('url_slug', $url_slug)->firstOrFail();

        // Check if user has access to edit this event
        $user = $request->user();
        $hasAccess = $event->user_id === $user->id ||
                     $event->users()->where('user_id', $user->id)->whereIn('role', ['owner', 'manager'])->exists();

        if (!$hasAccess) {
            abort(403, 'Nemáte oprávnenie na úpravu tohto podujatia.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url_slug' => 'required|string|max:255|unique:events,url_slug,' . $event->id,
            'description' => 'nullable|string',
            'start_time' => 'required|date',
            'registration_start' => 'required|date',
            'registration_end' => 'required|date|after:registration_start',
            'contact_name' => 'required|string|max:255',
            'contact_email' => 'required|email|max:255',
            'contact_phone' => 'nullable|string|max:255',
            'bank_account' => 'required|string|max:255',
            'multiple_reservations_per_ticket' => 'boolean',
            'background_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'remove_background_image' => 'boolean',
        ]);

        // Handle background image upload
        $backgroundImagePath = $event->background_image_path;

        // If user wants to remove the background image
        if ($request->boolean('remove_background_image')) {
            if ($backgroundImagePath) {
                Storage::disk('public')->delete($backgroundImagePath);
            }
            $backgroundImagePath = null;
        }

        // If a new image is uploaded
        if ($request->hasFile('background_image')) {
            // Delete old image if exists
            if ($backgroundImagePath) {
                Storage::disk('public')->delete($backgroundImagePath);
            }
            $backgroundImagePath = $request->file('background_image')->store('events/backgrounds', 'public');
        }

        $event->update([
            'title' => $validated['title'],
            'url_slug' => $validated['url_slug'],
            'description' => !empty($validated['description']) ? $validated['description'] : null,
            'start_time' => $validated['start_time'],
            'registration_start' => $validated['registration_start'],
            'registration_end' => $validated['registration_end'],
            'contact_name' => $validated['contact_name'],
            'contact_email' => $validated['contact_email'],
            'contact_phone' => !empty($validated['contact_phone']) ? $validated['contact_phone'] : null,
            'bank_account' => $validated['bank_account'],
            'multiple_reservations_per_ticket' => $validated['multiple_reservations_per_ticket'] ?? false,
            'background_image_path' => $backgroundImagePath,
        ]);

        return redirect()->route('event.manage', $event->url_slug)
            ->with('success', 'Podujatie bolo úspešne aktualizované!');
    }

    public function show($url_slug)
    {
        $event = Event::where('url_slug', $url_slug)
            ->with(['tickets', 'location', 'orders' => function($query) {
                $query->where('status', '!=', 'cancelled')
                      ->with('tickets');
            }])
            ->firstOrFail();

        $tickets = $event->tickets;

        $totalReservedSeats = $event->computeReservedSeats();

        return Inertia::render('event/Show', [
            'event' => array_merge($event->only([
                'id',
                'title',
                'start_time',
                'url_slug',
                'seats_total',
                'registration_start',
                'registration_end',
                'user_id',
                'description',
                'multiple_reservations_per_ticket',
                'background_image_path'
            ])),
            'location' => $event->location,
            'tickets' => $tickets,
            'places_left' => $event->seats_total - $totalReservedSeats,
        ]);
    }
}
