<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    use HasFactory;

    protected $table = 'feedbacks';

    /** type: complaint | suggestion | appreciation | info */
    /** status: new | in_progress | resolved | closed */
    protected $fillable = [
        'ticket', 'name', 'phone', 'email', 'department_id',
        'type', 'subject', 'message', 'status',
        'ip_address', 'submitted_at', 'resolved_at',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'resolved_at'  => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public static function generateTicket(): string
    {
        return 'LHN-' . now()->format('Ymd') . '-' . str_pad((string) random_int(1, 9999), 4, '0', STR_PAD_LEFT);
    }
}
