<?php

namespace App\Models;

use App\Enums\LeadStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payment extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'lead_id',
        'amount',
        'lead_status',
        'status',
        'lan',
        'misc',
        'agent_id',
        'completed_at',
    ];

    protected $casts = [
        'lead_status' => LeadStatus::class,
        'status' => PaymentStatus::class,
        'misc' => 'array',
    ];

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }

    public function responses(): HasMany
    {
        return $this->hasMany(PaymentResponse::class);
    }
}
