<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Achievement extends Model
{
    use HasFactory;

    protected $fillable = ['year', 'title', 'description', 'image_url', 'sort_order', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];
}
