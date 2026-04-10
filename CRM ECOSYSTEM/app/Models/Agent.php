<?php

namespace App\Models;

use App\Enums\AgentType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Agent extends Model
{    
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'type',
        'targets',
        'password_reset',
        'chargebacks',
    ];

    protected $casts = [
        'type' => AgentType::class,
        'targets' => 'array',
        'password_reset' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class);
    }

    public function boLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'bo_agent_id');
    }

    public function csLeads(): HasMany
    {
        return $this->hasMany(Lead::class, 'cs_agent_id');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function csvUploads(): HasMany
    {
        return $this->hasMany(CSVUpload::class);
    }

    public function paymentResponses(): HasMany
    {
        return $this->hasMany(PaymentResponse::class);
    }

    public function break(): HasOne
    {
        return $this->hasOne(AgentBreak::class)->where('active', true);
    }

    public function breaks(): HasMany
    {
        return $this->hasMany(AgentBreak::class);
    }

    public function myChargebacks(): HasMany
    {
        return $this->hasMany(Chargeback::class);
    }
    
    public function history(): HasMany
    {
        return $this->hasMany(LeadHistory::class);
    }

    // Event: Delete linked records
    public static function boot()
    {
        parent::boot();
        self::deleting(function($agent) {
            $agent->leads()->update([
                'agent_id' => null,
                'callback' => null,
            ]);
        });
    }
}
