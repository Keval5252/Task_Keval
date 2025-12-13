<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biometric_identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('biometric_device_id')->constrained('biometric_devices')->cascadeOnDelete();
            $table->string('external_user_id'); // the fingerprint enroll/user id on the device (often numeric but stored as string)
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['biometric_device_id', 'external_user_id'], 'bio_identity_device_external_unique');
            $table->index(['user_id', 'biometric_device_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_identities');
    }
};

