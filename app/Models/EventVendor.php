<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class EventVendor extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'event_id',
        'event_series_id',
        'vendor_id',
        'service_scope',
        'cost_amount',
    ];
}
