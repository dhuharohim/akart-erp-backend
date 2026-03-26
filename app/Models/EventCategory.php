<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'event_series_id',
        'name',
        'quota',
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

    public function prices(): HasMany
    {
        return $this->hasMany(EventCategoryPrice::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function prizes(): HasMany
    {
        return $this->hasMany(EventCategoryPrize::class);
    }

    public function getRemainingQuotaAttribute(): int
    {
        return $this->quota - $this->registrations()
            ->whereIn('payment_status', ['paid', 'pending'])
            ->count();
    }
}
