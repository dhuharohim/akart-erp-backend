<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventTeamAssignment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'event_series_id',
        'team_id',
        'role_in_event',
        'cost_amount',
        'attendance',
    ];

    protected function casts(): array
    {
        return [
            'attendance' => 'boolean',
        ];
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function eventSeries(): BelongsTo
    {
        return $this->belongsTo(EventSeries::class);
    }
}
