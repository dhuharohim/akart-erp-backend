<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sponsor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'sponsor_code',
        'name',
        'email',
        'phone',
        'website',
        'description',
    ];

    public function seriesAssignments(): HasMany
    {
        return $this->hasMany(EventSeriesSponsor::class);
    }

    public function getRouteKeyName(): string
    {
        return 'sponsor_code';
    }
}
