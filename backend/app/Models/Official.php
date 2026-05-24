<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Official extends Model
{
    use HasFactory;

    /** role: mayor | deputy | cao | other */
    protected $fillable = [
        'role', 'name', 'designation', 'photo', 'phone', 'email', 'message', 'sort_order', 'is_active',
    ];

    protected $casts = ['is_active' => 'boolean'];
}
