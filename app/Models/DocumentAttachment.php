<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentAttachment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'document_id',
        'file_name',
        'file_path',
        'file_type',
        'disk',
        'uploaded_by',
    ];

    public function document(): BelongsTo
    {
        return $this->belongsTo(Document::class, 'document_id');
    }
}
