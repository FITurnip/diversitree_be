<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model as Eloquent; // Use MongoDB's Model class
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Eloquent // MongoDB Eloquent Model
{
    use Notifiable, HasApiTokens;

    protected $collection = 'users';  // Define the collection name

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];
}
