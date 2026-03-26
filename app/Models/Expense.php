<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Expense extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'event_series_id',
        'account_id',
        'purchase_order_id',
        'description',
        'amount',
        'expense_date',
    ];

    public function account(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(ChartOfAccount::class, 'account_id');
    }

    protected function casts(): array
    {
        return [
            'expense_date' => 'date',
        ];
    }
}
