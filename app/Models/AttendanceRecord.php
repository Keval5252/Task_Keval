<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'shift_id',
        'source',
        'source_biometric_device_id',
        'source_biometric_punch_event_id',
        'clock_in_at',
        'clock_out_at',
        'scheduled_start_at',
        'scheduled_end_at',
        'total_minutes',
        'regular_minutes',
        'overtime_minutes',
    ];

    protected $casts = [
        'clock_in_at' => 'datetime',
        'clock_out_at' => 'datetime',
        'scheduled_start_at' => 'datetime',
        'scheduled_end_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function sourceBiometricDevice(): BelongsTo
    {
        return $this->belongsTo(BiometricDevice::class, 'source_biometric_device_id');
    }

    public function sourceBiometricPunchEvent(): BelongsTo
    {
        return $this->belongsTo(BiometricPunchEvent::class, 'source_biometric_punch_event_id');
    }
}

