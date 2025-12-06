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

