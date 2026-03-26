<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>{{ $title ?? 'Document' }}</title>
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 12px; color: #0f172a; margin: 0; }
        .page { padding: 32px; }
        .brand { border-bottom: 2px solid #8ba52e; padding-bottom: 8px; margin-bottom: 18px; }
        .brand h1 { margin: 0; font-size: 16px; color: #1f2937; }
        .brand p { margin: 4px 0 0; font-size: 11px; color: #475569; }
        .title { margin: 0 0 14px; font-size: 20px; color: #0f172a; }
        .meta { margin-bottom: 14px; color: #334155; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        td { border: 1px solid #cbd5e1; padding: 7px 8px; vertical-align: top; }
        td:first-child { width: 220px; background: #f8fafc; font-weight: 600; }
        .watermark { position: fixed; top: 45%; left: 12%; opacity: .09; font-size: 52px; transform: rotate(-20deg); color: #0f172a; }
        .signature { margin-top: 24px; padding-top: 8px; border-top: 1px dashed #94a3b8; color: #334155; }
        .footer { margin-top: 20px; font-size: 10px; color: #64748b; text-align: right; }
    </style>
</head>
<body>
@if(!empty($watermark_text))
    <div class="watermark">{{ $watermark_text }}</div>
@endif
<div class="page">
    <div class="brand">
        <h1>AKART ERP</h1>
        <p>Event Documentation System</p>
    </div>
    <h2 class="title">{{ $title ?? 'Document' }}</h2>
    <p class="meta">Generated at: {{ $generated_at ?? now()->toDateTimeString() }}</p>
    <table>
        @foreach(($payload ?? []) as $field => $value)
            <tr>
                <td>{{ str_replace('_', ' ', ucfirst($field)) }}</td>
                <td>{{ is_array($value) ? implode(', ', $value) : ($value ?? '-') }}</td>
            </tr>
        @endforeach
    </table>
    @if(!empty($digital_signature))
        <div class="signature">
            Digital Signature: {{ $digital_signature }}
        </div>
    @endif
    <div class="footer">AKART ERP Document Engine</div>
</div>
</body>
</html>
