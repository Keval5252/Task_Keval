@extends('employee.layout')

@section('title', 'Attendance Report')

@section('employee_content')
<div class="card border-0 shadow-sm">
    <div class="card-body">
        <h5 class="card-title mb-3">Attendance Report</h5>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Shift</th>
                        <th>Clock In</th>
                        <th>Clock Out</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Regular</th>
                        <th class="text-end">Overtime</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($records as $r)
                        <tr>
                            <td>{{ $r->clock_in_at?->format('Y-m-d') }}</td>
                            <td>{{ $r->shift?->name ?? '-' }}</td>
                            <td>{{ $r->clock_in_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $r->clock_out_at?->format('Y-m-d H:i') ?? 'In progress' }}</td>
                            <td class="text-end">
                                @if($r->clock_out_at)
                                    {{ intdiv($r->total_minutes, 60) }}h {{ $r->total_minutes % 60 }}m
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end">
                                @if($r->clock_out_at)
                                    {{ intdiv($r->regular_minutes, 60) }}h {{ $r->regular_minutes % 60 }}m
                                @else
                                    —
                                @endif
                            </td>
                            <td class="text-end">
                                @if($r->clock_out_at)
                                    {{ intdiv($r->overtime_minutes, 60) }}h {{ $r->overtime_minutes % 60 }}m
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary">No attendance records found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div>
            {{ $records->links() }}
        </div>
    </div>
</div>
@endsection
