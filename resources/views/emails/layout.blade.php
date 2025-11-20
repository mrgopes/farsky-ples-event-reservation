<!DOCTYPE html>
<html lang="sk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'Email' }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f4f4f4;
        }
        .email-container {
            background-color: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .header {
            background-color: #000000;
            color: white;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
        }
        .content {
            background-color: #f9f9f9;
            padding: 30px 20px;
            border: 1px solid #ddd;
        }
        .content p {
            margin: 15px 0;
        }
        .info-section {
            margin: 20px 0;
        }
        .info-section h3 {
            color: #000000;
            margin-bottom: 10px;
        }
        .detail-row {
            margin: 8px 0;
        }
        .label {
            font-weight: bold;
        }
        .event-info {
            background-color: #f9fafb;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border-left: 4px solid #000000;
        }
        .event-info strong {
            color: #000000;
        }
        .tickets-table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }
        .tickets-table th, .tickets-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .tickets-table th {
            background-color: #000000;
            color: white;
        }
        .total {
            font-size: 1.2em;
            font-weight: bold;
            text-align: right;
            margin-top: 15px;
            color: #000000;
        }
        .button-container {
            text-align: center;
            margin: 30px 0;
        }
        .button {
            display: inline-block;
            background-color: #000000;
            color: white;
            padding: 15px 30px;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
            font-size: 16px;
        }
        .button:hover {
            background-color: #333333;
        }
        .order-number {
            background-color: #f3f4f6;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            margin: 20px 0;
            font-family: monospace;
            font-size: 14px;
        }
        .link-fallback {
            word-break: break-all;
            color: #000000;
            font-size: 12px;
            margin-top: 15px;
        }
        .footer {
            background-color: #f9fafb;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <div class="header">
            <h1>{{ $heading ?? 'Email' }}</h1>
        </div>

        <div class="content">
            @yield('content')
        </div>

        <div class="footer">
            <p>Toto je automaticky generovaný email. Prosím neodpovedajte naň.</p>
            <p style="margin-top: 10px;">© {{ date('Y') }} Farský ples Čierna Voda - Všetky práva vyhradené</p>
            <p style="margin-top: 10px;">V prípade technických problémov sa obráťte na <a
                    href="mailto:podpora@farskyplesciernavoda.sk"></a>podpora@farskyplesciernavoda.sk</p>
        </div>
    </div>
</body>
</html>

