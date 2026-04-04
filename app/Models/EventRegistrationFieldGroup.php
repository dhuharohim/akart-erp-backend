<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EventRegistrationFieldGroup extends Model
{
    protected $fillable = [
        'event_id',
        'event_series_id',
        'name',
        'sort_order',
    ];

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(EventSeries::class, 'event_series_id');
    }

    public function fields(): HasMany
    {
        return $this->hasMany(EventRegistrationField::class, 'field_group_id')->orderBy('sort_order');
    }
}
