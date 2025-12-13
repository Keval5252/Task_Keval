<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BiometricDevice extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'identifier',
        'api_key_hash',
        'timezone',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected $hidden = [
        'api_key_hash',
    ];

    public function identities(): HasMany
    {
        return $this->hasMany(BiometricIdentity::class);
    }

    public function punchEvents(): HasMany
    {
        return $this->hasMany(BiometricPunchEvent::class);
    }

    public static function hashApiKey(string $apiKey): string
    {
        return hash('sha256', $apiKey);
    }
}

