@extends('emails.layout', ['heading' => 'Objednávka zrušená', 'title' => 'Objednávka zrušená'])

@section('content')
    <p>Dobrý deň {{ $order->name }},</p>

    <p>Vaša objednávka č. <strong>{{ $order->variable_symbol }}</strong> pre podujatie <strong>{{ optional($order->event)->title }}</strong> bola zrušená.</p>

    <p style="color: #e74c3c; font-weight: bold;">Častý dôvod na zrušenie objednávky je jej nezaplatenie včas.</p>

    <div class="info-section">
        <h3>Detaily podujatia</h3>
        <div class="detail-row">
            <span class="label">Názov:</span> {{ optional($order->event)->overline  }} {{ optional($order->event)->title }}
        </div>
        <div class="detail-row">
            <span class="label">Dátum:</span> {{ optional(optional($order->event)->start_time) ? \Carbon\Carbon::parse($order->event->start_time)->format('d.m.Y H:i') : '' }}
        </div>
        @if(optional($order->event)->location)
        <div class="detail-row">
            <span class="label">Miesto:</span> {{ optional($order->event->location)->address }}
        </div>
        @endif
    </div>

    <p>Ak máte akékoľvek otázky alebo si myslíte, že ide o omyl, neváhajte nás kontaktovať.</p>

    @include('emails.partials.contact-info')
@endsection
