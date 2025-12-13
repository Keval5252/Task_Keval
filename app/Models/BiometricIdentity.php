<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BiometricIdentity extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'biometric_device_id',
        'external_user_id',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function device(): BelongsTo
    {
        return $this->belongsTo(BiometricDevice::class, 'biometric_device_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function punchEvents(): HasMany
    {
        return $this->hasMany(BiometricPunchEvent::class);
    }
}

