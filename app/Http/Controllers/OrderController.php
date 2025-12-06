<?php

namespace App\Http\Controllers;

use App\Mail\OrderConfirmation;
use App\Mail\OrderPending;
use App\Mail\OrderCancelled;
use App\Models\Event;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function create($url_slug)
    {
        $event = Event::where('url_slug', $url_slug)
            ->with(['tickets', 'reservations.order', 'location'])
            ->firstOrFail();

        // Get seat numbers only from orders that are not cancelled
        $activeReservations = $event->reservations
            ->filter(fn($reservation) => $reservation->order->status !== 'cancelled');

        $reservedSeatNumbers = $activeReservations
            ->pluck('seat_number')
            ->unique()
            ->values()
            ->all();

        // Create a mapping of seat_number => guest_name for hover tooltips
        $seatNames = $activeReservations
            ->mapWithKeys(fn($reservation) => [$reservation->seat_number => $reservation->guest_name])
            ->all();

        return Inertia::render('event/Order', [
            // Shape the event payload and include reservations as an array of seat numbers
            'event' => array_merge($event->only([
                'id',
                'title',
                'start_time',
                'url_slug',
                'seats_total',
                'registration_start',
                'registration_end',
                'user_id',
                'address',
                'contact_name',
                'contact_phone',
                'contact_email',
            ]), [
                'reservations' => $reservedSeatNumbers,
                'seat_names' => $seatNames,
                'location' => optional($event->location)->svg_map,
            ]),
            'location' => $event->location,
            'tickets' => $event->tickets,
        ]);
    }

    public function store(Request $request, Event $event)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email'],
            'phone' => ['required', 'string', 'max:30'],
            'tickets' => ['required', 'array'],
            'seats' => ['required', 'array'],
            'guests' => ['required', 'array'],
        ]);

        $requestedSeats = array_values($request->seats);
        $reservedSeats = Reservation::whereIn('seat_number', $requestedSeats)
            ->whereHas('order', function ($query) use ($event) {
                $query->where('event_id', $event->id)
                      ->where('status', '!=', 'cancelled');
            })
            ->pluck('seat_number')
            ->toArray();

        if (!empty($reservedSeats)) {
            return back()->withErrors([
                'seats' => 'Niektoré z vybratých miest sú už rezervované: ' . implode(', ', $reservedSeats)
            ])->withInput();
        }

        // Capacity check: count currently reserved seats for this event (exclude cancelled orders)
        $currentlyReservedCount = Reservation::whereHas('order', function ($query) use ($event) {
            $query->where('event_id', $event->id)
                  ->where('status', '!=', 'cancelled');
        })->count();

        $requestedCount = count($requestedSeats);

        if (($currentlyReservedCount + $requestedCount) > ($event->seats_total ?? 0)) {
            $available = max(0, ($event->seats_total ?? 0) - $currentlyReservedCount);
            return back()->withErrors([
                'seats' => 'Nie je dosť voľných miest. Zostáva ' . $available . ' miest.'
            ])->withInput();
        }

        $order = new Order();
        $order->event_id = $event->id;
        $order->name = $request->name;
        $order->email = $request->email;
        $order->phone = $request->phone;
        $order->status = 'pending';
        $order->variable_symbol = (string) random_int(1000000000, 9999999999);
//        $order->payment_note = 'VS' . $order->variable_symbol;
        $order->url_slug = (string) Str::uuid();
        $order->save();

        foreach ($request->tickets as $ticketId => $amount) {
            $amount = (int) $amount;
            if ($amount <= 0) {
                continue;
            }

            // Ensure the ticket belongs to the same event to prevent cross-event attachment
            if (! Ticket::where('id', $ticketId)->where('event_id', $event->id)->exists()) {
                continue;
            }

            $order->tickets()->syncWithoutDetaching([
                $ticketId => ['amount' => $amount],
            ]);
        }

        foreach ($request->seats as $seatId => $seatNumber) {
            $reservation = new Reservation();
            $reservation->order()->associate($order);
            $reservation->seat_number = $seatNumber;
            $reservation->guest_name = $request->guests[$seatId] ?? 'Guest';
            $reservation->save();
        }

        // Send confirmation email
        $this->sendOrderPendingEmail($order);

        return redirect()
            ->route('order.sent', ['order' => $order])
            ->with('success', 'Vaša objednávka bola úspešne vytvorená!');
    }

    /**
     * Send order confirmation email to the customer
     */
    protected function sendOrderConfirmationEmail(Order $order): void
    {
        try {
            // Load relationships needed for the email
            $order->load('event.location', 'tickets', 'reservations');

            // Send the email
            Mail::to($order->email)->send(new OrderConfirmation($order));
        } catch (\Exception $e) {
            // Log the error but don't fail the order creation
            Log::error('Failed to send order confirmation email: ' . $e->getMessage());
        }
    }

    /**
     * Send order pending notification email to the customer
     */
    protected function sendOrderPendingEmail(Order $order): void
    {
        try {
            // Load relationships needed for the email
            $order->load('event');

            // Send the email
            Mail::to($order->email)->send(new OrderPending($order));
        } catch (\Exception $e) {
            // Log the error but don't fail the order creation
            Log::error('Failed to send order pending email: ' . $e->getMessage());
        }
    }

    protected function sendOrderCancelledEmail(Order $order): void
    {
        try {
            // Load relationships needed for the email
            $order->load('event');

            // Send the email
            Mail::to($order->email)->send(new \App\Mail\OrderCancelled($order));
        } catch (\Exception $e) {
            // Log the error but don't fail the order creation
            Log::error('Failed to send order cancelled email: ' . $e->getMessage());
        }
    }

    public function sent(Order $order)
    {
        $order->load('event.location', 'tickets', 'reservations');

        $tickets = $order->tickets->map(function ($ticket) {
            return [
                'id' => $ticket->id,
                'title' => $ticket->title,
                'price' => $ticket->price,
                'reservations' => $ticket->reservations,
                'amount' => $ticket->pivot->amount ?? 1,
            ];
        });

        $event = $order->event;
        $shapedEvent = array_merge($event->toArray(), [
            'location' => optional($event->location)->name,
        ]);

        return Inertia::render('order/Sent', [
            'order' => $order->only(['id','name','email','phone','status', 'variable_symbol', 'payment_note', 'qr_code']),
            'event' => $shapedEvent,
            'tickets' => $tickets,
            'location' => $event->location,
            'reservations' => $order->reservations->map->only(['id','seat_number','guest_name', 'qr_code']),
        ]);
    }

    public function confirm(Order $order)
    {
        $order->update([
            'status' => 'paid'
        ]);

        $order->reservations->each(function ($reservation) {
            $reservation->qr_code = (string) Str::uuid();
            $reservation->save();
        });

        $this->sendOrderConfirmationEmail($order);

        return json_encode(['status' => 'success', 'message' => 'Order confirmed and email sent.']);
    }

    /**
     * Confirm an order from the event management page
     */
    public function confirmOrder(Request $request, $url_slug)
    {
        $order = Order::where('url_slug', $url_slug)->firstOrFail();
        $event = $order->event;

        // Check if user has permission to manage this event
        $user = $request->user();
        $hasAccess = $event->user_id === $user->id ||
                     $event->users()->where('user_id', $user->id)->whereIn('role', ['owner', 'manager'])->exists();

        if (!$hasAccess) {
            abort(403, 'Nemáte oprávnenie na správu tohto podujatia.');
        }

        // Update order status
        $order->update([
            'status' => 'paid'
        ]);

        // Generate QR codes for reservations if not already generated
        $order->reservations->each(function ($reservation) {
            if (!$reservation->qr_code) {
                $reservation->qr_code = (string) Str::uuid();
                $reservation->save();
            }
        });

        // Send confirmation email
        $this->sendOrderConfirmationEmail($order);

        return redirect()->back()->with('success', 'Objednávka bola úspešne potvrdená!');
    }

    /**
     * Cancel an order from the event management page
     */
    public function cancelOrder(Request $request, $url_slug)
    {
        $order = Order::where('url_slug', $url_slug)->firstOrFail();
        $event = $order->event;

        // Check if user has permission to manage this event
        $user = $request->user();
        $hasAccess = $event->user_id === $user->id ||
                     $event->users()->where('user_id', $user->id)->whereIn('role', ['owner', 'manager'])->exists();

        if (!$hasAccess) {
            abort(403, 'Nemáte oprávnenie na správu tohto podujatia.');
        }

        // Update order status
        $order->update([
            'status' => 'cancelled'
        ]);

        // Send cancellation email to the customer
        $this->sendOrderCancelledEmail($order);

        return redirect()->back()->with('success', 'Objednávka bola zrušená.');
    }
}
