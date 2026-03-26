<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventRegistration extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'event_series_id',
        'event_category_id',
        'event_price_series_id',
        'registration_number',
        'idempotency_key',
        'first_name',
        'last_name',
        'email',
        'telephone',
        'age',
        'address',
        'custom_fields',
        'amount',
        'payment_status',
        'xendit_invoice_id',
        'xendit_invoice_url',
        'xendit_external_id',
        'paid_at',
        'present_status',
        'checked_in_at',
        'scanned_by_type',
        'scanned_by_id',
    ];

    protected function casts(): array
    {
        return [
            'custom_fields' => 'array',
            'paid_at' => 'datetime',
            'checked_in_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(EventSeries::class, 'event_series_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function priceSeries(): BelongsTo
    {
        return $this->belongsTo(EventPriceSeries::class, 'event_price_series_id');
    }

    public function scannedBy(): MorphTo
    {
        return $this->morphTo('scanned_by');
    }

    public function attendanceRecords(): MorphMany
    {
        return $this->morphMany(AttendanceRecord::class, 'attendable');
    }
}
