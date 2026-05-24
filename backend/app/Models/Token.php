<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Token extends Model
{
    use HasFactory;

    /** status: waiting | serving | done | cancelled */
    protected $fillable = [
        'department_id', 'code', 'sequence', 'name', 'phone',
        'status', 'issued_at', 'served_at', 'closed_at',
    ];

    protected $casts = [
        'issued_at' => 'datetime',
        'served_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    /** Issue next token for a department for today, atomically. */
    public static function issue(Department $dept): self
    {
        return \DB::transaction(function () use ($dept) {
            $today = now()->startOfDay();

            $next = static::where('department_id', $dept->id)
                ->where('issued_at', '>=', $today)
                ->lockForUpdate()
                ->max('sequence') + 1;

            $prefix = strtoupper(substr(preg_replace('/[^a-z]/i', '', $dept->slug), 0, 3) ?: 'GEN');

            return static::create([
                'department_id' => $dept->id,
                'code'          => sprintf('%s-%03d', $prefix, $next),
                'sequence'      => $next,
                'status'        => 'waiting',
                'issued_at'     => now(),
            ]);
        });
    }
}
