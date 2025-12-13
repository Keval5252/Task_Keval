<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('biometric_punch_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('biometric_device_id')->constrained('biometric_devices')->cascadeOnDelete();
            $table->foreignId('biometric_identity_id')->nullable()->constrained('biometric_identities')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();

            // Optional id from the device log to allow idempotency.
            $table->string('event_uid')->nullable();

            $table->dateTime('punched_at');
            $table->string('punch_type')->nullable(); // "in" | "out" | "auto"

            $table->foreignId('attendance_record_id')->nullable()->constrained('attendance_records')->nullOnDelete();
            $table->dateTime('processed_at')->nullable();

            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->unique(['biometric_device_id', 'event_uid'], 'bio_punch_device_eventuid_unique');
            $table->index(['user_id', 'punched_at']);
            $table->index(['biometric_device_id', 'punched_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('biometric_punch_events');
    }
};

