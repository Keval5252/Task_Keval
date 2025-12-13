<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\BiometricDevice;
use App\Models\BiometricIdentity;
use App\Models\BiometricPunchEvent;
use App\Models\ShiftAssignment;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\DB;

class BiometricPunchProcessor
{
    /**
     * Process a biometric punch event and (optionally) create/close an attendance record.
     *
     * Expected input keys:
     * - external_user_id (string)
     * - punched_at (date string)
     * - punch_type ("in"|"out"|"auto"|null)
     * - event_uid (string|null)
     */
    public function process(BiometricDevice $device, array $data): array
    {
        $externalUserId = (string) ($data['external_user_id'] ?? '');
        $eventUid = isset($data['event_uid']) ? (string) $data['event_uid'] : null;
        $requestedType = isset($data['punch_type']) ? (string) $data['punch_type'] : 'auto';

        $tz = $device->timezone ?: config('app.timezone');
        $punchedAt = CarbonImmutable::parse($data['punched_at'], $tz);

        return DB::transaction(function () use ($device, $externalUserId, $eventUid, $requestedType, $punchedAt, $data) {
            if ($eventUid !== null && $eventUid !== '') {
                $existing = BiometricPunchEvent::query()
                    ->where('biometric_device_id', $device->id)
                    ->where('event_uid', $eventUid)
                    ->with('attendanceRecord')
                    ->first();

                if ($existing) {
                    return [
                        'ok' => true,
                        'idempotent' => true,
                        'action' => 'already_processed',
                        'event' => $existing,
                        'attendance' => $existing->attendanceRecord,
                    ];
                }
            } else {
                $eventUid = null;
            }

            /** @var BiometricIdentity|null $identity */
            $identity = BiometricIdentity::query()
                ->where('biometric_device_id', $device->id)
                ->where('external_user_id', $externalUserId)
                ->where('active', true)
                ->with('user')
                ->first();

            $event = BiometricPunchEvent::create([
                'biometric_device_id' => $device->id,
                'biometric_identity_id' => $identity?->id,
                'user_id' => $identity?->user_id,
                'event_uid' => $eventUid,
                'punched_at' => $punchedAt,
                'punch_type' => in_array($requestedType, ['in', 'out', 'auto'], true) ? $requestedType : 'auto',
                'raw_payload' => $data,
            ]);

            if (!$identity || !$identity->user) {
                $event->processed_at = now();
                $event->save();

                return [
                    'ok' => true,
                    'idempotent' => false,
                    'action' => 'ignored_unknown_user',
                    'event' => $event->fresh(),
                    'attendance' => null,
                ];
            }

            $user = $identity->user;

            /** @var AttendanceRecord|null $open */
            $open = AttendanceRecord::query()
                ->where('user_id', $user->id)
                ->whereNull('clock_out_at')
                ->latest('clock_in_at')
                ->with('shift')
                ->first();

            $type = $requestedType;
            if (!in_array($type, ['in', 'out', 'auto'], true)) {
                $type = 'auto';
            }
            if ($type === 'auto') {
                $type = $open ? 'out' : 'in';
            }

            if ($type === 'in') {
                if ($open) {
                    $event->attendance_record_id = $open->id;
                    $event->processed_at = now();
                    $event->save();

                    return [
                        'ok' => true,
                        'idempotent' => false,
                        'action' => 'ignored_already_clocked_in',
                        'event' => $event->fresh('attendanceRecord'),
                        'attendance' => $open,
                    ];
                }

                $assignment = $this->resolveAssignment($user->id, $punchedAt);
                if (!$assignment || !$assignment->shift) {
                    $event->processed_at = now();
                    $event->save();

                    return [
                        'ok' => true,
                        'idempotent' => false,
                        'action' => 'ignored_no_shift_assigned',
                        'event' => $event->fresh(),
                        'attendance' => null,
                    ];
                }

                $shift = $assignment->shift;

                $calc = WorkTimeCalculator::calculate(
                    $punchedAt,
                    $punchedAt->addMinute(),
                    $shift->start_time,
                    $shift->end_time,
                );

                $record = AttendanceRecord::create([
                    'user_id' => $user->id,
                    'shift_id' => $shift->id,
                    'source' => 'biometric',
                    'source_biometric_device_id' => $device->id,
                    'source_biometric_punch_event_id' => $event->id,
                    'clock_in_at' => $punchedAt,
                    'scheduled_start_at' => $calc['scheduled_start_at'],
                    'scheduled_end_at' => $calc['scheduled_end_at'],
                ]);

                $event->attendance_record_id = $record->id;
                $event->processed_at = now();
                $event->save();

                return [
                    'ok' => true,
                    'idempotent' => false,
                    'action' => 'clocked_in',
                    'event' => $event->fresh(),
                    'attendance' => $record->fresh(),
                ];
            }

            // type === 'out'
            if (!$open) {
                $event->processed_at = now();
                $event->save();

                return [
                    'ok' => true,
                    'idempotent' => false,
                    'action' => 'ignored_no_open_attendance',
                    'event' => $event->fresh(),
                    'attendance' => null,
                ];
            }

            $clockInAt = CarbonImmutable::instance($open->clock_in_at);

            if ($open->shift) {
                $calc = WorkTimeCalculator::calculate(
                    $clockInAt,
                    $punchedAt,
                    $open->shift->start_time,
                    $open->shift->end_time,
                );

                $open->scheduled_start_at = $calc['scheduled_start_at'];
                $open->scheduled_end_at = $calc['scheduled_end_at'];
                $open->total_minutes = $calc['total_minutes'];
                $open->regular_minutes = $calc['regular_minutes'];
                $open->overtime_minutes = $calc['overtime_minutes'];
            } else {
                $totalMinutes = max(0, $clockInAt->diffInMinutes($punchedAt));
                $open->total_minutes = $totalMinutes;
                $open->regular_minutes = 0;
                $open->overtime_minutes = $totalMinutes;
            }

            $open->clock_out_at = $punchedAt;
            $open->save();

            $event->attendance_record_id = $open->id;
            $event->processed_at = now();
            $event->save();

            return [
                'ok' => true,
                'idempotent' => false,
                'action' => 'clocked_out',
                'event' => $event->fresh(),
                'attendance' => $open->fresh(),
            ];
        });
    }

    private function resolveAssignment(int $userId, CarbonImmutable $at): ?ShiftAssignment
    {
        return ShiftAssignment::query()
            ->where('user_id', $userId)
            ->whereDate('effective_from', '<=', $at->toDateString())
            ->where(function ($q) use ($at) {
                $q->whereNull('effective_to')
                    ->orWhereDate('effective_to', '>=', $at->toDateString());
            })
            ->with('shift')
            ->orderByDesc('effective_from')
            ->first();
    }
}

