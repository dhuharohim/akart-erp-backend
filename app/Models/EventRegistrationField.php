<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistrationField extends Model
{
    protected $fillable = [
        'event_id',
        'event_series_id',
        'field_name',
        'field_label',
        'field_type',
        'is_required',
        'is_enabled',
        'options',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_required' => 'boolean',
            'is_enabled' => 'boolean',
            'options' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
