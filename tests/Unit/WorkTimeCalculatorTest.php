<?php

namespace Tests\Unit;

use App\Services\WorkTimeCalculator;
use Carbon\CarbonImmutable;
use PHPUnit\Framework\TestCase;

class WorkTimeCalculatorTest extends TestCase
{
    public function test_day_shift_overtime_example(): void
    {
        $in = CarbonImmutable::parse('2025-12-13 08:00:00', 'UTC');
        $out = CarbonImmutable::parse('2025-12-13 23:00:00', 'UTC');

        $calc = WorkTimeCalculator::calculate($in, $out, '08:00:00', '20:00:00');

        $this->assertSame(900, $calc['total_minutes']);   // 15h
        $this->assertSame(720, $calc['regular_minutes']); // 12h
        $this->assertSame(180, $calc['overtime_minutes']); // 3h
    }

    public function test_overnight_shift_regular_time(): void
    {
        // Shift: 20:00 -> next day 08:00 (12h)
        $in = CarbonImmutable::parse('2025-12-13 20:00:00', 'UTC');
        $out = CarbonImmutable::parse('2025-12-14 08:00:00', 'UTC');

        $calc = WorkTimeCalculator::calculate($in, $out, '20:00:00', '08:00:00');

        $this->assertSame(720, $calc['total_minutes']);
        $this->assertSame(720, $calc['regular_minutes']);
        $this->assertSame(0, $calc['overtime_minutes']);
        $this->assertSame('2025-12-13 20:00:00', $calc['scheduled_start_at']->format('Y-m-d H:i:s'));
        $this->assertSame('2025-12-14 08:00:00', $calc['scheduled_end_at']->format('Y-m-d H:i:s'));
    }
}

