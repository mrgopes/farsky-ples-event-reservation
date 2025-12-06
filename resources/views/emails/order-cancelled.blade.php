<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Objednávka zrušená</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h1 style="color: #e74c3c;">Objednávka zrušená</h1>

        <p>Dobrý deň {{ $order->name }},</p>

        <p>Vaša objednávka č. <strong>{{ $order->variable_symbol }}</strong> pre podujatie <strong>{{ optional($order->event)->title }}</strong> bola zrušená.</p>

        <div style="background-color: #f8f9fa; padding: 15px; border-radius: 5px; margin: 20px 0;">
            <h3 style="margin-top: 0;">Detaily podujatia:</h3>
            <p style="margin: 5px 0;"><strong>Názov:</strong> {{ optional($order->event)->title }}</p>
            <p style="margin: 5px 0;"><strong>Dátum:</strong> {{ optional(optional($order->event)->start_time) ? \Carbon\Carbon::parse($order->event->start_time)->format('d.m.Y H:i') : '' }}</p>
            @if(optional($order->event)->location)
            <p style="margin: 5px 0;"><strong>Miesto:</strong> {{ optional($order->event->location)->name }}</p>
            @endif
        </div>

        <p>Ak máte akékoľvek otázky, neváhajte nás kontaktovať.</p>

        <p>S pozdravom,<br>{{ config('app.name') }}</p>
    </div>
</body>
</html>

