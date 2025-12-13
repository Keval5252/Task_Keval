<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class WorkTimeCalculator
{
    /**
     * Calculates total/regular/overtime minutes given a clock-in/out and shift times.
     *
     * Rules:
     * - Regular minutes are the overlap between worked interval and scheduled shift interval.
     * - Overtime minutes are worked minutes outside the scheduled interval.
     * - Overnight shifts are supported (end_time <= start_time means "next day").
     *
     * @param  CarbonImmutable  $clockInAt
     * @param  CarbonImmutable  $clockOutAt
     * @param  string  $shiftStartTime  "HH:MM:SS"
     * @param  string  $shiftEndTime    "HH:MM:SS"
     * @return array{scheduled_start_at: CarbonImmutable, scheduled_end_at: CarbonImmutable, total_minutes:int, regular_minutes:int, overtime_minutes:int}
     */
    public static function calculate(
        CarbonImmutable $clockInAt,
        CarbonImmutable $clockOutAt,
        string $shiftStartTime,
        string $shiftEndTime,
    ): array {
        if ($clockOutAt->lessThanOrEqualTo($clockInAt)) {
            return [
                'scheduled_start_at' => $clockInAt,
                'scheduled_end_at' => $clockInAt,
                'total_minutes' => 0,
                'regular_minutes' => 0,
                'overtime_minutes' => 0,
            ];
        }

        $start = CarbonImmutable::parse($shiftStartTime, $clockInAt->getTimezone());
        $end = CarbonImmutable::parse($shiftEndTime, $clockInAt->getTimezone());

        $isOvernight = $end->lessThanOrEqualTo($start);

        // Anchor date for schedule: for overnight shifts, early-morning clock-ins belong to previous day's shift start.
        $anchorDate = $clockInAt->toDateString();
        if ($isOvernight && $clockInAt->format('H:i:s') < $shiftEndTime) {
            $anchorDate = $clockInAt->subDay()->toDateString();
        }

        $scheduledStartAt = CarbonImmutable::parse($anchorDate . ' ' . $shiftStartTime, $clockInAt->getTimezone());
        $scheduledEndAt = CarbonImmutable::parse($anchorDate . ' ' . $shiftEndTime, $clockInAt->getTimezone());
        if ($isOvernight) {
            $scheduledEndAt = $scheduledEndAt->addDay();
        }

        $totalMinutes = $clockInAt->diffInMinutes($clockOutAt);

        // Overlap calculation.
        $overlapStart = $clockInAt->greaterThan($scheduledStartAt) ? $clockInAt : $scheduledStartAt;
        $overlapEnd = $clockOutAt->lessThan($scheduledEndAt) ? $clockOutAt : $scheduledEndAt;

        $regularMinutes = 0;
        if ($overlapEnd->greaterThan($overlapStart)) {
            $regularMinutes = $overlapStart->diffInMinutes($overlapEnd);
        }

        $overtimeMinutes = max(0, $totalMinutes - $regularMinutes);

        return [
            'scheduled_start_at' => $scheduledStartAt,
            'scheduled_end_at' => $scheduledEndAt,
            'total_minutes' => $totalMinutes,
            'regular_minutes' => $regularMinutes,
            'overtime_minutes' => $overtimeMinutes,
        ];
    }
}

