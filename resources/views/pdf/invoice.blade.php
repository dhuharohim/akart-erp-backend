<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $invoice->invoice_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #e5e7eb; padding: 8px; text-align: left; }
    </style>
</head>
<body>
    <h1>Invoice {{ $invoice->invoice_number }}</h1>
    <p>Status: {{ $invoice->status }}</p>
    <p>Issue Date: {{ $invoice->issue_date }}</p>
    <p>Due Date: {{ $invoice->due_date }}</p>
    <h2>Total: {{ number_format((float) $invoice->total_amount, 2) }}</h2>
</body>
</html>
