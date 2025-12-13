<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BiometricPunchEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'biometric_device_id',
        'biometric_identity_id',
        'user_id',
        'event_uid',
        'punched_at',
        'punch_type',
        'attendance_record_id',
        'processed_at',
        'raw_payload',
    ];

    protected $casts = [
        'punched_at' => 'datetime',
        'processed_at' => 'datetime',
        'raw_payload' => 'array',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(BiometricDevice::class, 'biometric_device_id');
    }

    public function identity(): BelongsTo
    {
        return $this->belongsTo(BiometricIdentity::class, 'biometric_identity_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attendanceRecord(): BelongsTo
    {
        return $this->belongsTo(AttendanceRecord::class);
    }
}

