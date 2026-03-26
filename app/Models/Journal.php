<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Journal extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'journal_number',
        'date',
        'description',
        'reference',
        'status',
        'total_debit',
        'total_credit',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'journal_number';
    }

    public function lines(): HasMany
    {
        return $this->hasMany(JournalLine::class);
    }
}
