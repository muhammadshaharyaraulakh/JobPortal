<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Otp extends Model
{
    protected $fillable = [
        'email',
        'purpose',
        'otp',
        'expires_at',
        'payload',
        'attempts',
    ];
    protected $casts = [
        'expires_at' => 'datetime',
        'payload' => 'array',
    ];
}
