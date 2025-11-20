@extends('emails.layout', ['heading' => 'Potvrdenie objednávky', 'title' => 'Potvrdenie objednávky'])

@section('content')
    <p>Dobrý deň {{ $order->name }},</p>

    <p>Ďakujeme za Vašu objednávku! Toto je potvrdenie Vašej rezervácie.</p>

    <div class="info-section">
        <h3>Informácie o podujatí</h3>
        <div class="detail-row">
            <span class="label">Názov:</span> {{ $order->event->title }}
        </div>
        <div class="detail-row">
            <span class="label">Dátum a čas:</span> {{ \Carbon\Carbon::parse($order->event->start_time)->format('d.m.Y H:i') }}
        </div>
        @if($order->event->location)
        <div class="detail-row">
            <span class="label">Miesto:</span> {{ $order->event->location->name }}
        </div>
        @endif
        @if($order->event->address)
        <div class="detail-row">
            <span class="label">Adresa:</span> {{ $order->event->address }}
        </div>
        @endif
    </div>

    <div class="info-section">
        <h3>Vaše údaje</h3>
        <div class="detail-row">
            <span class="label">Meno:</span> {{ $order->name }}
        </div>
        <div class="detail-row">
            <span class="label">Email:</span> {{ $order->email }}
        </div>
        <div class="detail-row">
            <span class="label">Telefón:</span> {{ $order->phone }}
        </div>
    </div>

    <div class="info-section">
        <h3>Vstupenky</h3>
        <table class="tickets-table">
            <thead>
                <tr>
                    <th>Typ vstupenky</th>
                    <th>Počet</th>
                    <th>Cena</th>
                    <th>Spolu</th>
                </tr>
            </thead>
            <tbody>
                @php $total = 0; @endphp
                @foreach($order->tickets as $ticket)
                @php
                    $amount = $ticket->pivot->amount ?? 1;
                    $subtotal = $ticket->price * $amount;
                    $total += $subtotal;
                @endphp
                <tr>
                    <td>{{ $ticket->title }}</td>
                    <td>{{ $amount }}</td>
                    <td>{{ number_format($ticket->price, 2) }} €</td>
                    <td>{{ number_format($subtotal, 2) }} €</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        <div class="total">
            Celková suma: {{ number_format($total, 2) }} €
        </div>
    </div>

    @if($order->reservations->count() > 0)
    <div class="info-section">
        <h3>Rezervované miesta</h3>
        @foreach($order->reservations as $reservation)
        <div class="detail-row">
            <span class="label">Miesto {{ $reservation->seat_number }}:</span> {{ $reservation->guest_name }}
        </div>
        @endforeach
    </div>
    @endif

    <div class="info-section">
        <h3>Platobné informácie</h3>
        <div class="detail-row">
            <span class="label">Variabilný symbol:</span> {{ $order->variable_symbol }}
        </div>
        <div class="detail-row">
            <span class="label">Poznámka:</span> {{ $order->payment_note }}
        </div>
        <div class="detail-row">
            <span class="label">Stav objednávky:</span> {{ $order->status }}
        </div>
    </div>

    <p style="margin-top: 20px;">
        V prípade akýchkoľvek otázok nás neváhajte kontaktovať.
    </p>

    @if($order->event->contact_email || $order->event->contact_phone)
    <div class="info-section">
        <h3>Kontakt</h3>
        @if($order->event->contact_name)
        <div class="detail-row">
            <span class="label">Meno:</span> {{ $order->event->contact_name }}
        </div>
        @endif
        @if($order->event->contact_email)
        <div class="detail-row">
            <span class="label">Email:</span> {{ $order->event->contact_email }}
        </div>
        @endif
        @if($order->event->contact_phone)
        <div class="detail-row">
            <span class="label">Telefón:</span> {{ $order->event->contact_phone }}
        </div>
        @endif
    </div>
    @endif
@endsection
