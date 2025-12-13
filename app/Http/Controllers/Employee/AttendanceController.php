<?php

namespace App\Http\Controllers\Employee;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\ShiftAssignment;
use App\Services\WorkTimeCalculator;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function dashboard(Request $request)
    {
        $user = $request->user();
        $user->load(['currentShiftAssignment.shift']);

        $open = AttendanceRecord::query()
            ->where('user_id', $user->id)
            ->whereNull('clock_out_at')
            ->latest('clock_in_at')
            ->with('shift')
            ->first();

        $latest = AttendanceRecord::query()
            ->where('user_id', $user->id)
            ->latest('clock_in_at')
            ->with('shift')
            ->limit(10)
            ->get();

        return view('employee.dashboard', [
            'open' => $open,
            'latest' => $latest,
            'assignment' => $user->currentShiftAssignment,
        ]);
    }

    public function attendanceReport(Request $request)
    {
        $user = $request->user();

        $records = AttendanceRecord::query()
            ->where('user_id', $user->id)
            ->with('shift')
            ->latest('clock_in_at')
            ->paginate(50)
            ->withQueryString();

        return view('employee.attendance.index', [
            'records' => $records,
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
            toastr()->error('You already have an open attendance record. Please clock out first.');
            return redirect()->route('employee.dashboard');
        }

        $now = CarbonImmutable::now();

        $assignment = $this->resolveAssignment($user->id, $now);
        if (!$assignment) {
            toastr()->error('No shift assigned for today. Please contact admin.');
            return redirect()->route('employee.dashboard');
        }

        $shift = $assignment->shift;

        $calc = WorkTimeCalculator::calculate(
            $now,
            $now->addMinute(),
            $shift->start_time,
            $shift->end_time,
        );

        AttendanceRecord::create([
            'user_id' => $user->id,
            'shift_id' => $shift->id,
            'clock_in_at' => $now,
            'scheduled_start_at' => $calc['scheduled_start_at'],
            'scheduled_end_at' => $calc['scheduled_end_at'],
        ]);

        toastr()->success('Clock-in successful!');
        return redirect()->route('employee.dashboard');
    }

    public function clockOut(Request $request)
    {
        $user = $request->user();

        /** @var AttendanceRecord|null $record */
        $record = AttendanceRecord::query()
            ->where('user_id', $user->id)
            ->whereNull('clock_out_at')
            ->latest('clock_in_at')
            ->with('shift')
            ->first();

        if (!$record) {
            toastr()->error('No open attendance record found. Please clock in first.');
            return redirect()->route('employee.dashboard');
        }

        if (!$record->shift) {
            toastr()->error('Shift missing on attendance record. Please contact admin.');
            return redirect()->route('employee.dashboard');
        }

        $now = CarbonImmutable::now();
        $clockInAt = CarbonImmutable::instance($record->clock_in_at);

        $calc = WorkTimeCalculator::calculate(
            $clockInAt,
            $now,
            $record->shift->start_time,
            $record->shift->end_time,
        );

        $record->clock_out_at = $now;
        $record->scheduled_start_at = $calc['scheduled_start_at'];
        $record->scheduled_end_at = $calc['scheduled_end_at'];
        $record->total_minutes = $calc['total_minutes'];
        $record->regular_minutes = $calc['regular_minutes'];
        $record->overtime_minutes = $calc['overtime_minutes'];
        $record->save();

        toastr()->success('Clock-out successful!');
        return redirect()->route('employee.dashboard');
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
