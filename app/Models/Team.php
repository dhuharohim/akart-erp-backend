<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'team_code',
        'name',
        'description',
        'lead_employee_id',
    ];

    public function leadEmployee(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'lead_employee_id');
    }

    public function employees(): HasMany
    {
        return $this->hasMany(Employee::class);
    }

    public function eventAssignments(): HasMany
    {
        return $this->hasMany(EventTeamAssignment::class);
    }
}
