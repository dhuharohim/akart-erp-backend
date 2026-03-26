<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventSeriesSponsor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'event_series_id',
        'sponsor_id',
        'contribution_amount',
        'notes',
        'term_conditions',
    ];

    public function sponsor(): BelongsTo
    {
        return $this->belongsTo(Sponsor::class);
    }

    public function eventSeries(): BelongsTo
    {
        return $this->belongsTo(EventSeries::class, 'event_series_id');
    }
}
