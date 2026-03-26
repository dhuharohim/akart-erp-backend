<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class EventStaff extends Model
{
    use HasFactory;

    protected $fillable = [
        'event_id',
        'event_series_id',
        'employee_id',
        'employee_number',
        'role_in_event',
        'attendance',
        'cost_amount',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function attendanceRecords(): MorphMany
    {
        return $this->morphMany(AttendanceRecord::class, 'attendable');
    }
}
