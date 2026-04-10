<?php

namespace App\Models;

use App\Enums\CustomerStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'country',
        'residence',
        'dob',
        'gender',
        'status',
        'phone_number',
        'office_number',
        'profession',
        'marital_status',
        'education',
        'language',
        'occupation',
        'experience',
        'arrange_after_employment',
        'spouse',
    ];

    protected $casts = [
        'status' => CustomerStatus::class,
        'spouse' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function lead(): HasOne
    {
        return $this->hasOne(Lead::class);
    }

    // Event: Delete linked records
    public static function boot()
    {
        parent::boot();
        self::deleting(function($customer) {
            if($customer->lead) {
                $customer->lead->delete();
            }
        });
    }
}
