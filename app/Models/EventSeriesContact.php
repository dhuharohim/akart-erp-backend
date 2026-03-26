<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventSeriesContact extends Model
{
    protected $fillable = [
        'event_series_id',
        'event_staff_id',
        'label',
        'sort_order',
    ];

    public function series(): BelongsTo
    {
        return $this->belongsTo(EventSeries::class, 'event_series_id');
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(EventStaff::class, 'event_staff_id');
    }
}
