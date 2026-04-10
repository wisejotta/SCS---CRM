<?php

namespace App\Models;

use App\Enums\LeadStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lead extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'status',
        'visa_id',
        'agent_id',
        'customer_id',
        'complete',
        'phone_number',
        'email',
        'callback',
        'bo_callback',
        'assigned_at',
        'language',
        'reassigned_at',
        'unassigned_at',
        'callback_agent_id',
        'callback_bo_agent_id',
        'recall',
        'bo_agent_id',
        'cs_agent_id',
        'sort_date',
        'reason',
        'results',
        'backoffice',
        'mailchimp_id',
    ];

    protected $casts = [
        'status' => LeadStatus::class,
        'reason' => 'array',
        'backoffice' => 'array',
        'results' => 'array',
    ];
    
    public function history(): HasMany
    {
        return $this->hasMany(LeadHistory::class);
    }
    
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class)->withTrashed();
    }

    public function cbAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'callback_agent_id')->withTrashed();
    }

    public function cbBOAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'callback_bo_agent_id')->withTrashed();
    }

    public function boAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'bo_agent_id')->withTrashed();
    }

    public function csAgent(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'cs_agent_id')->withTrashed();
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
    
    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function visa(): BelongsTo
    {
        return $this->belongsTo(Visa::class);
    }

    public function responses(): HasManyThrough
    {
        return $this->hasManyThrough(PaymentResponse::class, Payment::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function retainers(): HasMany
    {
        return $this->hasMany(Retainer::class);
    }

    // Event: Delete linked records
    public static function boot()
    {
        parent::boot();
        self::deleting(function($lead) {
            $lead->history()->delete();
            $lead->comments()->delete();
            $lead->payments()->delete();
            $lead->responses()->delete();
            $lead->documents()->delete();
        });
    }
}
