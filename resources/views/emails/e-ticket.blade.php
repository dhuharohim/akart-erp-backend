<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>E-Ticket</title>
</head>
<body style="font-family:Arial,sans-serif;background:#f8fafc;color:#0f172a;padding:20px;">
    <div style="max-width:560px;margin:0 auto;background:#ffffff;border:1px solid #e2e8f0;border-radius:12px;padding:20px;">
        <h2 style="margin:0 0 8px;">Your E-Ticket</h2>
        <p style="margin:0 0 12px;">{{ $eventName }} - {{ $seriesName }}</p>
        <p style="margin:0 0 16px;">
            Registration Number:
            <strong>{{ $registrationNumber }}</strong>
        </p>
        <p style="margin:0 0 16px;">
            Open your e-ticket:
            <a href="{{ $ticketUrl }}">{{ $ticketUrl }}</a>
        </p>
        <p style="margin:0;color:#64748b;font-size:12px;">
            Please keep this registration number for check-in.
        </p>
    </div>
</body>
</html>
