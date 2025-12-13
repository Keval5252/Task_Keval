<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BiometricDevice;
use App\Services\BiometricPunchProcessor;
use Illuminate\Http\Request;

class BiometricPunchController extends Controller
{
    public function punch(Request $request, BiometricPunchProcessor $processor)
    {
        /** @var BiometricDevice|null $device */
        $device = $request->attributes->get('biometric_device');
        if (!$device) {
            return response()->json([
                'ok' => false,
                'error' => 'Biometric device not authenticated.',
            ], 401);
        }

        $validated = $request->validate([
            'external_user_id' => ['required', 'string', 'max:191'],
            'punched_at' => ['required', 'date'],
            'punch_type' => ['nullable', 'string', 'in:in,out,auto'],
            'event_uid' => ['nullable', 'string', 'max:191'],
        ]);

        // Keep extra fields for audit/debug.
        $payload = array_merge($request->all(), $validated);

        $result = $processor->process($device, $payload);

        return response()->json($result);
    }
}

