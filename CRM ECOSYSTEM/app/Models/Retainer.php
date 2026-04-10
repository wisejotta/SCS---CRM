<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Retainer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'signed_at',
        'file',
        'lead_id',
        'visa_id',
        'results',
        'results2',
    ];

    protected $casts = [
        'results2' => 'array',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function visa(): BelongsTo
    {
        return $this->belongsTo(Visa::class);
    }
}
