@extends('emails.layout', ['heading' => '📋 Objednávka bola vytvorená', 'title' => '📋 Objednávka bola vytvorená'])

@section('content')
    <p>Dobrý deň <strong>{{ $order->name }}</strong>,</p>

    <p>Vaša objednávka bola úspešne vytvorená a čaká na zaplatenie.</p>

    <div class="event-info">
        <strong>Podujatie:</strong> {{ $order->event->title }}<br>
        <strong>Dátum:</strong> {{ \Carbon\Carbon::parse($order->event->start_time)->format('d.m.Y H:i') }}
    </div>

    <div class="order-number">
        <strong>IBAN:</strong> {{ $order->event->bank_account }}<br>
        <strong>Variabilný symbol:</strong> {{ $order->variable_symbol }} <br>
        <strong>Suma:</strong> {{ number_format($order->getTotalPrice(), 2) }} €
    </div>

    <p>Pre zobrazenie stavu objednávky a detailných informácií kliknite na tlačidlo nižšie:</p>

    <div class="button-container">
        <a href="{{ route('order.sent', ['order' => $order->url_slug]) }}" class="button">
            Zobraziť objednávku
        </a>
    </div>

    <p class="link-fallback">
        Ak tlačidlo nefunguje, skopírujte tento odkaz do prehliadača:<br>
        {{ route('order.sent', ['order' => $order->url_slug]) }}
    </p>

    <p>Na tejto stránke môžete sledovať aktuálny stav objednávky, vidieť všetky detaily a platobné informácie.</p>

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
