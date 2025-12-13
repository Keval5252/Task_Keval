<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->string('source')->nullable()->after('shift_id'); // "manual" | "biometric" | etc.
            $table->foreignId('source_biometric_device_id')
                ->nullable()
                ->after('source')
                ->constrained('biometric_devices')
                ->nullOnDelete();

            $table->foreignId('source_biometric_punch_event_id')
                ->nullable()
                ->after('source_biometric_device_id')
                ->constrained('biometric_punch_events')
                ->nullOnDelete();

            $table->index(['source', 'clock_in_at']);
        });
    }

    public function down(): void
    {
        Schema::table('attendance_records', function (Blueprint $table) {
            $table->dropIndex(['source', 'clock_in_at']);
            $table->dropConstrainedForeignId('source_biometric_punch_event_id');
            $table->dropConstrainedForeignId('source_biometric_device_id');
            $table->dropColumn('source');
        });
    }
};

