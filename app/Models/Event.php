<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Event extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'event_number',
        'name',
        'description',
        'type',
        'status',
        'timeline',
        'registration_config',
        'budget_amount',
        'revenue_amount',
        'expense_amount',
        'profit_amount',
    ];

    protected function casts(): array
    {
        return [
            'timeline' => 'array',
            'registration_config' => 'array',
        ];
    }

    public function series(): HasMany
    {
        return $this->hasMany(EventSeries::class);
    }

    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function priceSeries(): HasMany
    {
        return $this->hasMany(EventPriceSeries::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(EventCategory::class);
    }

    public function registrationFields(): HasMany
    {
        return $this->hasMany(EventRegistrationField::class);
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }
}
