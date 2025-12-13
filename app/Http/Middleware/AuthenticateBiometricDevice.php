<?php

namespace App\Http\Middleware;

use App\Models\BiometricDevice;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateBiometricDevice
{
    public function handle(Request $request, Closure $next): Response
    {
        $identifier = (string) $request->header('X-Biometric-Device', '');
        $apiKey = (string) $request->header('X-Biometric-Key', '');

        if ($identifier === '' || $apiKey === '') {
            return response()->json([
                'ok' => false,
                'error' => 'Missing biometric device credentials.',
            ], 401);
        }

        /** @var BiometricDevice|null $device */
        $device = BiometricDevice::query()
            ->where('identifier', $identifier)
            ->first();

        if (!$device) {
            return response()->json([
                'ok' => false,
                'error' => 'Unknown biometric device.',
            ], 401);
        }

        if (!$device->active) {
            return response()->json([
                'ok' => false,
                'error' => 'Biometric device is disabled.',
            ], 403);
        }

        $providedHash = BiometricDevice::hashApiKey($apiKey);
        if (!hash_equals($device->api_key_hash, $providedHash)) {
            return response()->json([
                'ok' => false,
                'error' => 'Invalid biometric device credentials.',
            ], 401);
        }

        $request->attributes->set('biometric_device', $device);

        return $next($request);
    }
}

