<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Ticket;
use Illuminate\Support\Facades\DB;

class TicketController extends Controller
{
    /**
     * Store a new ticket for an event.
     */
    public function store(Request $request, $event_slug)
    {
        $event = Event::where('url_slug', $event_slug)->firstOrFail();

        // Check if user has access to manage this event
        $user = $request->user();
        $hasAccess = $event->user_id === $user->id ||
                     $event->users()->where('user_id', $user->id)->whereIn('role', ['owner', 'manager'])->exists();

        if (!$hasAccess) {
            abort(403, 'Nemáte oprávnenie na správu tohto podujatia.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'reservations' => 'required|integer|min:1',
        ]);

        $ticket = Ticket::create([
            'event_id' => $event->id,
            'title' => $validated['title'],
            'price' => $validated['price'],
            'reservations' => $validated['reservations'],
        ]);

        return redirect()->route('event.manage', $event->url_slug)
            ->with('success', 'Typ lístka bol úspešne vytvorený!');
    }

    /**
     * Update an existing ticket.
     */
    public function update(Request $request, $event_slug, $ticket_id)
    {
        $event = Event::where('url_slug', $event_slug)->firstOrFail();
        $ticket = Ticket::where('id', $ticket_id)->where('event_id', $event->id)->firstOrFail();

        // Check if user has access to manage this event
        $user = $request->user();
        $hasAccess = $event->user_id === $user->id ||
                     $event->users()->where('user_id', $user->id)->whereIn('role', ['owner', 'manager'])->exists();

        if (!$hasAccess) {
            abort(403, 'Nemáte oprávnenie na správu tohto podujatia.');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'reservations' => 'required|integer|min:1',
        ]);

        $ticket->update([
            'title' => $validated['title'],
            'price' => $validated['price'],
            'reservations' => $validated['reservations'],
        ]);

        return redirect()->route('event.manage', $event->url_slug)
            ->with('success', 'Typ lístka bol úspešne aktualizovaný!');
    }

    /**
     * Delete a ticket.
     */
    public function destroy(Request $request, $event_slug, $ticket_id)
    {
        $event = Event::where('url_slug', $event_slug)->firstOrFail();
        $ticket = Ticket::where('id', $ticket_id)->where('event_id', $event->id)->firstOrFail();

        // Check if user has access to manage this event
        $user = $request->user();
        $hasAccess = $event->user_id === $user->id ||
                     $event->users()->where('user_id', $user->id)->whereIn('role', ['owner', 'manager'])->exists();

        if (!$hasAccess) {
            abort(403, 'Nemáte oprávnenie na správu tohto podujatia.');
        }

        // Check if ticket has any orders
        $hasOrders = DB::table('order_ticket')->where('ticket_id', $ticket->id)->exists();

        if ($hasOrders) {
            return redirect()->route('event.manage', $event->url_slug)
                ->with('error', 'Tento typ lístka nemôže byť odstránený, pretože má existujúce objednávky.');
        }

        $ticket->delete();

        return redirect()->route('event.manage', $event->url_slug)
            ->with('success', 'Typ lístka bol úspešne odstránený!');
    }
}
