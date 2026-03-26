<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Vendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'company_id',
        'vendor_code',
        'name',
        'email',
        'phone',
        'address',
        'vendor_service_id',
        'rating',
    ];

    public function getRouteKeyName(): string
    {
        return 'vendor_code';
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(VendorService::class, 'vendor_service_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(VendorItem::class);
    }
}
