<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;      // Laravel base user
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\Access\Authorizable as AuthorizableTrait; // Laravel authorizable trait

class User extends Authenticatable
{
    use Notifiable, AuthorizableTrait;

    protected $table = 'api_users';

    protected $fillable = [
        'name',
        'email',
        'cliente_id',
        'api_token',
        'usuario_id',
        'last_login',
        'api_client',
        'channel',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'api_token', // hide if you don’t want it in API responses
    ];

    // Optional: modern casting (adjust as needed)
    protected $casts = [
        'email_verified_at' => 'datetime',
        'last_login'        => 'integer',
    ];
}
