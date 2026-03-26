<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Purchase Order {{ $purchase_order->po_number }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #111827; }
    </style>
</head>
<body>
    <h1>Purchase Order {{ $purchase_order->po_number }}</h1>
    <p>Status: {{ $purchase_order->status }}</p>
    <p>Vendor ID: {{ $purchase_order->vendor_id }}</p>
    <h2>Total: {{ number_format((float) $purchase_order->total_amount, 2) }}</h2>
</body>
</html>
