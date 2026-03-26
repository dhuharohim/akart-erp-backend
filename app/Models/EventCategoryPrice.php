<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventCategoryPrice extends Model
{
    protected $fillable = [
        'event_price_series_id',
        'event_category_id',
        'price',
        'quota',
    ];

    public function series(): BelongsTo
    {
        return $this->belongsTo(EventPriceSeries::class, 'event_price_series_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }

    public function getRemainingQuotaAttribute(): int
    {
        return $this->quota - EventRegistration::where('event_category_id', $this->event_category_id)
            ->where('event_price_series_id', $this->event_price_series_id)
            ->whereIn('payment_status', ['paid', 'pending'])
            ->count();
    }
}
