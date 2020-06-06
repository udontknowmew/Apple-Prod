<?php

namespace App;

use Illuminate\Auth\Authenticatable;
use Laravel\Lumen\Auth\Authorizable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Model implements JWTSubject, AuthenticatableContract
{
    use Authenticatable;

    protected $fillable = [
      'email', 'password', 'password_ori', 'country', 'user_agent'
    ];

    protected $hidden   = [
      'password', 'password_ori', 'created_at', 'updated_at'
    ];

    public function billing() {
      return $this->hasMany('App\Billing');
    }

    public function card() {
      return $this->hasMany('App\Card');
    }

    public function getJWTIdentifier()
    {
      return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
      return [];
    }
}
