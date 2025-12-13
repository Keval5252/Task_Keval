<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\ShiftAssignment;
use App\Services\WorkTimeCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $limit = max(1, min(100, (int) $request->query('limit', 30)));

        $records = AttendanceRecord::query()
            ->where('user_id', $user->id)
            ->with('shift')
            ->latest('clock_in_at')
            ->limit($limit)
            ->get();

        return response()->json([
            'data' => $records,
        ]);
    }

    public function clockIn(Request $request)
    {
        $user = $request->user();

        $open = AttendanceRecord::query()
            ->where('user_id', $user->id)
            ->whereNull('clock_out_at')
            ->latest('clock_in_at')
            ->first();

        if ($open) {
            throw ValidationException::withMessages([
                'clock_in' => ['You already have an open attendance record. Please clock out first.'],
            ]);
        }

        $now = CarbonImmutable::now();

        $assignment = $this->resolveAssignment($user->id, $now);
        if (!$assignment) {
            throw ValidationException::withMessages([
                'shift' => ['No shift assigned for today. Please contact admin.'],
            ]);
        }

        $shift = $assignment->shift;

        // Pre-capture schedule window for later calculations.
        $calc = WorkTimeCalculator::calculate(
            $now,
            $now->addMinute(), // dummy 1-min interval just to derive scheduled window
            $shift->start_time,
            $shift->end_time
        );

        $record = AttendanceRecord::create([
            'user_id' => $user->id,
            'shift_id' => $shift->id,
            'clock_in_at' => $now,
            'scheduled_start_at' => $calc['scheduled_start_at'],
            'scheduled_end_at' => $calc['scheduled_end_at'],
        ]);

        return response()->json([
            'ok' => true,
            'attendance' => $record,
        ]);
    }

    public function clockOut(Request $request)
    {
        $user = $request->user();

        /** @var AttendanceRecord|null $record */
        $record = AttendanceRecord::query()
            ->where('user_id', $user->id)
            ->whereNull('clock_out_at')
            ->latest('clock_in_at')
            ->first();

        if (!$record) {
            throw ValidationException::withMessages([
                'clock_out' => ['No open attendance record found. Please clock in first.'],
            ]);
        }

        $now = CarbonImmutable::now();
        $clockInAt = CarbonImmutable::instance($record->clock_in_at);

        $shift = $record->shift;
        if (!$shift) {
            throw ValidationException::withMessages([
                'shift' => ['Shift missing on attendance record. Please contact admin.'],
            ]);
        }

        $calc = WorkTimeCalculator::calculate(
            $clockInAt,
            $now,
            $shift->start_time,
            $shift->end_time
        );

        $record->clock_out_at = $now;
        $record->scheduled_start_at = $calc['scheduled_start_at'];
        $record->scheduled_end_at = $calc['scheduled_end_at'];
        $record->total_minutes = $calc['total_minutes'];
        $record->regular_minutes = $calc['regular_minutes'];
        $record->overtime_minutes = $calc['overtime_minutes'];
        $record->save();

        return response()->json([
            'ok' => true,
            'attendance' => $record->fresh(),
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user();

        $latest = AttendanceRecord::query()
            ->where('user_id', $user->id)
            ->latest('clock_in_at')
            ->first();

        return response()->json([
            'user' => $user,
            'latest_attendance' => $latest,
        ]);
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

