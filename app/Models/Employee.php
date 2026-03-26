<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'department_id',
        'team_id',
        'employee_code',
        'employee_type',
        'full_name',
        'email',
        'phone',
        'position',
        'salary',
        'hourly_rate',
        'project_rate',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function getRouteKeyName(): string
    {
        return 'employee_code';
    }


    public function ledTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'lead_employee_id');
    }
}
