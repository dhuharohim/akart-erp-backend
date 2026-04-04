<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class EventSeries extends Model
{
    use HasFactory, SoftDeletes;

    protected static function booted(): void
    {
        static::creating(function (self $series) {
            if ($series->require_ticketing && empty($series->public_id)) {
                $series->public_id = Str::uuid()->toString();
            }
            if (empty($series->checkin_passcode)) {
                $series->checkin_passcode = strtoupper(Str::random(6));
            }
        });

        static::updating(function (self $series) {
            if ($series->isDirty('require_ticketing') && $series->require_ticketing && empty($series->public_id)) {
                $series->public_id = Str::uuid()->toString();
            }
        });
    }

    public function regeneratePasscode(): string
    {
        $this->checkin_passcode = strtoupper(Str::random(6));
        $this->save();

        return $this->checkin_passcode;
    }

    public static function generateEmployeeNumber(string $employeeType, string $seriesNumber, int $seriesId): string
    {
        $prefix = strtoupper(substr($employeeType, 0, 3));
        $seriesNum = strtoupper(str_replace(['-', ' '], '', $seriesNumber));
        $count = EventStaff::where('event_series_id', $seriesId)->count() + 1;

        return $prefix.'-'.$seriesNum.'-'.str_pad((string) $count, 4, '0', STR_PAD_LEFT);
    }

    protected $fillable = [
        'event_id',
        'series_number',
        'public_id',
        'venue_id',
        'name',
        'status',
        'description',
        'start_date',
        'end_date',
        'budget_amount',
        'time_schedule',
        'implementation_instruction',
        'technical_instruction',
        'registration_config',
        'checkin_passcode',
        'sort_order',
        'require_ticketing',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'registration_config' => 'array',
            'require_ticketing' => 'boolean',
        ];
    }

    // Finance
    public function budgets(): HasMany
    {
        return $this->hasMany(Budget::class);
    }

    public function sponsors(): HasMany
    {
        return $this->hasMany(EventSeriesSponsor::class);
    }

    public function expenses(): HasMany
    {
        return $this->hasMany(Expense::class);
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function venue(): BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function venues(): BelongsToMany
    {
        return $this->belongsToMany(Venue::class, 'event_series_venue')
            ->withPivot('start_datetime', 'end_datetime', 'sort_order', 'amount', 'description')
            ->withTimestamps()
            ->orderByPivot('sort_order');
    }

    // Registration
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

    public function registrationFieldGroups(): HasMany
    {
        return $this->hasMany(EventRegistrationFieldGroup::class)->orderBy('sort_order');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function categoryPrizes(): HasMany
    {
        return $this->hasMany(EventCategoryPrize::class, 'event_series_id');
    }

    // Assignments
    public function vendors(): HasMany
    {
        return $this->hasMany(EventVendor::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(EventStaff::class);
    }

    public function teamAssignments(): HasMany
    {
        return $this->hasMany(EventTeamAssignment::class);
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(EventSeriesContact::class);
    }

    public function purchaseOrders(): HasMany
    {
        return $this->hasMany(PurchaseOrder::class);
    }
}
