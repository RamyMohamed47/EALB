<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Contracts\JWTSubject;
use Tymon\JWTAuth\Exceptions\JWTException;



#[Fillable([
    'name',
    'email',
    'password',
    'phone',
    'address',
    'job_description',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements JWTSubject
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;


    public const ROLE_ADMIN = 'admin';

    public const ROLE_CUSTOMER = 'customer';



    public function isAdmin(): bool
    {
        try {
            return JWTAuth::parseToken()
                ->getPayload()
                ->get('role') === self::ROLE_ADMIN;
        }
        catch (JWTException) {
            return false;
        }
    }


    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    public function getJWTIdentifier(): mixed
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims(): array
    {
        return [
        'role' => $this->role,
        ];
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
