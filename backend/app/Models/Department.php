<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Department extends Model
{
    use HasFactory;

    protected $fillable = [
        'slug', 'name', 'name_en', 'summary', 'icon',
        'room', 'floor',
        'contact_person', 'contact_designation', 'phone', 'email',
        'timings', 'charter', 'public_url',
        'services', 'required_documents', 'process', 'fees', 'forms',
        'sort_order', 'is_active',
    ];

    protected $casts = [
        'services'           => 'array',
        'required_documents' => 'array',
        'process'            => 'array',
        'fees'               => 'array',
        'forms'              => 'array',
        'is_active'          => 'boolean',
    ];

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function notices(): HasMany
    {
        return $this->hasMany(Notice::class);
    }

    public function tokens(): HasMany
    {
        return $this->hasMany(Token::class);
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true)->orderBy('sort_order');
    }
}
