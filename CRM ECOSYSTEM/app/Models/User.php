<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'firstname',
        'lastname',
        'email',
        'password',
        'role',
        'text_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'text_password',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'role' => UserRole::class,
    ];

    public function agent(): HasOne
    {
        return $this->hasOne(Agent::class);
    }

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    // Event: Delete linked records
    public static function boot()
    {
        parent::boot();
        self::deleting(function($user) {
            if($user->agent) {
                $user->agent->delete();
            }

            if($user->customer) {
                $user->customer->delete();
            }
        });
    }
}
