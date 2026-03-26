<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PurchaseOrder extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'event_id',
        'event_series_id',
        'vendor_id',
        'procurement_type',
        'po_number',
        'po_date',
        'status',
        'total_amount',
        'approved_at',
    ];

    public function getRouteKeyName(): string
    {
        return 'po_number';
    }

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'po_date' => 'date',
        ];
    }

    public function vendor(): BelongsTo
    {
        return $this->belongsTo(Vendor::class);
    }

    public function series(): BelongsTo
    {
        return $this->belongsTo(EventSeries::class, 'event_series_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function assets(): HasMany
    {
        return $this->hasMany(Asset::class);
    }
}
