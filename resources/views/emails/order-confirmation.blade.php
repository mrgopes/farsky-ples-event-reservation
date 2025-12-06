@extends('emails.layout', ['heading' => 'Potvrdenie objednávky', 'title' => 'Potvrdenie objednávky'])

@section('content')
    <p>Dobrý deň {{ $order->name }},</p>

    <p>Ďakujeme za Vašu objednávku! Toto je potvrdenie jej zaplatenia.</p>

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
            <span class="label">Miesto:</span> {{ $order->event->location->address }}
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

    <p>Vaše vstupenky môžete zobraziť na tomto linku:</p>

    <div class="button-container">
        <a href="{{ route('order.sent', ['order' => $order->url_slug]) }}" class="button">
            Zobraziť objednávku
        </a>
    </div>

    <p class="link-fallback">
        Ak tlačidlo nefunguje, skopírujte tento odkaz do prehliadača:<br>
        {{ route('order.sent', ['order' => $order->url_slug]) }}
    </p>

    <div class="info-section">
        <h3>Platobné informácie</h3>
        <div class="detail-row">
            <span class="label">Variabilný symbol:</span> {{ $order->variable_symbol }}
        </div>
        @if($order->payment_note != null)
        <div class="detail-row">
            <span class="label">Poznámka:</span> {{ $order->payment_note }}
        </div>
        @endif
        <div class="detail-row">
            <span class="label">Stav objednávky:</span> {{ $order->status }}
        </div>
    </div>

    <p style="margin-top: 20px;">
        V prípade akýchkoľvek otázok nás neváhajte kontaktovať.
    </p>

    @include('emails.partials.contact-info')
@endsection
