@extends('employee.layout')

@section('title', 'Employee Dashboard')

@section('employee_content')
<div class="row g-3">
    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <h5 class="card-title mb-3">Today</h5>

                @if($assignment && $assignment->shift)
                    <div class="mb-3">
                        <div class="text-secondary">Assigned shift</div>
                        <div class="fw-semibold">
                            {{ $assignment->shift->name }}
                            <span class="text-secondary fw-normal">
                                ({{ \Carbon\Carbon::parse($assignment->shift->start_time)->format('H:i') }} - {{ \Carbon\Carbon::parse($assignment->shift->end_time)->format('H:i') }})
                            </span>
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning mb-3">No shift assigned for today.</div>
                @endif

                @if($open)
                    <div class="alert alert-info">
                        You are currently <strong>clocked in</strong> since
                        {{ $open->clock_in_at?->format('Y-m-d H:i') }}.
                    </div>
                    <form method="post" action="{{ route('employee.clockout') }}">
                        @csrf
                        <button class="btn btn-danger" type="submit">Clock Out</button>
                    </form>
                @else
                    <div class="alert alert-secondary">You are currently <strong>clocked out</strong>.</div>
                    <form method="post" action="{{ route('employee.clockin') }}">
                        @csrf
                        <button class="btn btn-success" type="submit" @if(!$assignment) disabled @endif>
                            Clock In
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="card-title mb-0">Recent Attendance</h5>
                    <a class="btn btn-outline-success btn-sm" href="{{ route('employee.attendance.index') }}">View report</a>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm align-middle">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Shift</th>
                                <th>Clock In</th>
                                <th>Clock Out</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($latest as $r)
                                <tr>
                                    <td>{{ $r->clock_in_at?->format('Y-m-d') }}</td>
                                    <td>{{ $r->shift?->name ?? '-' }}</td>
                                    <td>{{ $r->clock_in_at?->format('H:i') }}</td>
                                    <td>{{ $r->clock_out_at?->format('H:i') ?? '—' }}</td>
                                    <td class="text-end">
                                        @if($r->clock_out_at)
                                            {{ intdiv($r->total_minutes, 60) }}h {{ $r->total_minutes % 60 }}m
                                        @else
                                            —
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-secondary">No attendance yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
