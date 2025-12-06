<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ReservationController extends Controller
{
    /**
     * Show reservation details when a QR code is scanned.
     */
    public function show(Request $request, $qrCode)
    {
        $user = $request->user();

        // Find the reservation by QR code
        $reservation = Reservation::where('qr_code', $qrCode)
            ->with([
                'order.event.location',
                'order.event.user',
                'order.reservations',
                'order.tickets'
            ])
            ->firstOrFail();

        $event = $reservation->order->event;

        // Check if user has access to this event (owner, manager, or staff)
        $hasAccess = $event->user_id === $user->id ||
                     $event->users()->where('user_id', $user->id)->exists();

        if (!$hasAccess) {
            abort(403, 'Nemáte oprávnenie na zobrazenie tejto rezervácie.');
        }

        // Determine user role
        $userRole = 'owner';
        if ($event->user_id !== $user->id) {
            $pivotRole = $event->users()->where('user_id', $user->id)->first();
            $userRole = $pivotRole ? $pivotRole->pivot->role : 'staff';
        }

        // Prepare tickets data with amounts from pivot
        $tickets = $reservation->order->tickets->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'price' => $ticket->price,
                'amount' => $ticket->pivot->amount ?? 1,
            ];
        });

        // Prepare reservations data
        $reservations = $reservation->order->reservations->map(function ($res) {
            return [
                'id' => $res->id,
                'seat_number' => $res->seat_number,
                'guest_name' => $res->guest_name,
                'qr_code' => $res->qr_code,
            ];
        });

        return Inertia::render('reservation/Show', [
            'reservation' => [
                'id' => $reservation->id,
                'seat_number' => $reservation->seat_number,
                'guest_name' => $reservation->guest_name,
                'qr_code' => $reservation->qr_code,
            ],
            'order' => [
                'id' => $reservation->order->id,
                'name' => $reservation->order->name,
                'email' => $reservation->order->email,
                'phone' => $reservation->order->phone,
                'status' => $reservation->order->status,
                'variable_symbol' => $reservation->order->variable_symbol,
                'payment_note' => $reservation->order->payment_note,
            ],
            'event' => [
                'id' => $event->id,
                'title' => $event->title,
                'start_time' => $event->start_time,
                'url_slug' => $event->url_slug,
                'seats_total' => $event->seats_total,
                'contact_name' => $event->contact_name,
                'contact_email' => $event->contact_email,
                'contact_phone' => $event->contact_phone,
                'bank_account' => $event->bank_account,
                'location' => $event->location ? $event->location->name : '',
                'multiple_reservations_per_ticket' => $event->multiple_reservations_per_ticket ?? false,
            ],
            'location' => $event->location ? [
                'id' => $event->location->id,
                'address' => $event->location->address,
            ] : null,
            'tickets' => $tickets,
            'reservations' => $reservations,
            'userRole' => $userRole,
        ]);
    }
}
