<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class AttendanceRecord extends Model
{
    protected $fillable = [
        'event_series_id',
        'attendable_type',
        'attendable_id',
        'date',
        'checked_in_at',
        'scanned_by_type',
        'scanned_by_id',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'checked_in_at' => 'datetime',
        ];
    }

    public function attendable(): MorphTo
    {
        return $this->morphTo();
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(EventSeries::class, 'event_series_id');
    }

    public function scannedBy(): MorphTo
    {
        return $this->morphTo('scanned_by');
    }
}
