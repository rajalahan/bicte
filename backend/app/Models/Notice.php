<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notice extends Model
{
    use HasFactory;

    protected $fillable = [
        'department_id', 'title', 'summary', 'body',
        'category', 'attachment_url',
        'is_active', 'is_urgent', 'published_at', 'expires_at',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'is_urgent'    => 'boolean',
        'published_at' => 'datetime',
        'expires_at'   => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function scopePublished($q)
    {
        return $q->where('is_active', true)
                 ->where(fn ($q2) => $q2->whereNull('published_at')->orWhere('published_at', '<=', now()))
                 ->where(fn ($q2) => $q2->whereNull('expires_at')->orWhere('expires_at', '>=', now()))
                 ->orderByDesc('is_urgent')
                 ->orderByDesc('published_at');
    }
}
