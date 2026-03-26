<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Guest Book - {{ $event->name }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #000; }
        .header { text-align: center; padding: 20px 0 14px; border-bottom: 2px solid #000; margin-bottom: 14px; }
        .header .doc-title { font-size: 11px; text-transform: uppercase; letter-spacing: 2px; color: #555; margin-bottom: 6px; }
        .header h1 { font-size: 18px; color: #000; margin-bottom: 2px; }
        .header h2 { font-size: 13px; color: #333; font-weight: normal; }
        .header .date { font-size: 10px; color: #666; margin-top: 4px; }
        .meta-label { color: #666; text-transform: uppercase; font-size: 8px; letter-spacing: 0.5px; }
        table { width: 100%; border-collapse: collapse; margin-top: 8px; }
        th {
            background-color: #eee;
            border: 1px solid #999;
            padding: 5px 6px;
            text-align: left;
            font-size: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #333;
        }
        td {
            border: 1px solid #ccc;
            padding: 5px 6px;
            font-size: 10px;
            color: #000;
        }
        tr:nth-child(even) td { background-color: #f5f5f5; }
        .text-center { text-align: center; }
        .num-col { width: 25px; }
        .check { text-align: center; font-size: 12px; }
        .footer { margin-top: 20px; text-align: center; font-size: 9px; color: #999; }
    </style>
</head>
<body>
    <div class="header">
        <p class="doc-title">Guest Book</p>
        <h1>{{ $event->name }}</h1>
        <h2>{{ $series->name }}</h2>
        @if($series->start_date || $series->end_date)
            <p class="date">
                {{ $series->start_date ? \Carbon\Carbon::parse($series->start_date)->format('d M Y') : '' }}
                @if($series->end_date)
                    &mdash; {{ \Carbon\Carbon::parse($series->end_date)->format('d M Y') }}
                @endif
            </p>
        @endif
    </div>

    <div style="margin-bottom: 12px;">
        <table style="width: auto; border: none; margin: 0;">
            <tr style="border: none;">
                <td style="border: none; padding: 2px 0; width: 110px;"><span class="meta-label">Event Number</span></td>
                <td style="border: none; padding: 2px 0; color: #000;">: {{ $event->event_number }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 2px 0;"><span class="meta-label">Series Number</span></td>
                <td style="border: none; padding: 2px 0; color: #000;">: {{ $series->series_number }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 2px 0;"><span class="meta-label">Total Registrations</span></td>
                <td style="border: none; padding: 2px 0; color: #000;">: {{ $registrations->count() }}</td>
            </tr>
            <tr style="border: none;">
                <td style="border: none; padding: 2px 0;"><span class="meta-label">Generated</span></td>
                <td style="border: none; padding: 2px 0; color: #000;">: {{ now()->format('d M Y H:i') }}</td>
            </tr>
        </table>
    </div>

    <table>
        <thead>
            <tr>
                <th class="num-col text-center">No</th>
                <th>Name</th>
                <th>Category</th>
                <th class="text-center">Payment</th>
                @if(count($eventDates) > 0)
                    @foreach($eventDates as $d)
                        <th class="text-center" style="width: 60px;">{{ $d->format('d M') }}</th>
                    @endforeach
                @else
                    <th class="text-center" style="width: 80px;">Present</th>
                @endif
                <th style="width: 80px;" class="text-center">Signature</th>
            </tr>
        </thead>
        <tbody>
            @forelse($registrations as $index => $reg)
                @php
                    $dates = $reg->attendanceRecords
                        ? $reg->attendanceRecords->pluck('date')->map(fn($d) => $d->format('Y-m-d'))->toArray()
                        : [];
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td>{{ $reg->first_name }} {{ $reg->last_name }}</td>
                    <td>{{ $reg->category->name ?? '-' }}</td>
                    <td class="text-center">{{ ucfirst($reg->payment_status) }}</td>
                    @if(count($eventDates) > 0)
                        @foreach($eventDates as $d)
                            <td class="check">
                                @if(in_array($d->format('Y-m-d'), $dates))
                                    ✓
                                @endif
                            </td>
                        @endforeach
                    @else
                        <td class="check">
                            @if($reg->present_status === 'present')
                                ✓
                            @endif
                        </td>
                    @endif
                    <td>&nbsp;</td>
                </tr>
            @empty
                <tr>
                    <td colspan="{{ 5 + max(count($eventDates), 1) }}" class="text-center" style="padding: 20px; color: #999;">No registrations</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        Generated by akart ERP
    </div>
</body>
</html>
