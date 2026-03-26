<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventCategoryPrize extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'event_id',
        'event_series_id',
        'event_category_id',
        'rank',
        'prize_name',
        'prize_value',
        'prize_note',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'prize_value' => 'decimal:2',
        ];
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(EventSeries::class, 'event_series_id');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(EventCategory::class, 'event_category_id');
    }
}
