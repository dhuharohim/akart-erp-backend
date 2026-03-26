<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Venue extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'code',
        'name',
        'address',
        'phone',
        'max_capacity',
        'space_concept',
        'type',
    ];

    protected function casts(): array
    {
        return [
            'max_capacity' => 'integer',
        ];
    }

    public function facilities(): HasMany
    {
        return $this->hasMany(VenueFacility::class);
    }

    public function eventSeries(): BelongsToMany
    {
        return $this->belongsToMany(EventSeries::class, 'event_series_venue')
            ->withPivot('start_datetime', 'end_datetime', 'sort_order', 'amount', 'description')
            ->withTimestamps();
    }
}
