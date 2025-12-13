@extends('admin.layout')

@section('title', 'Attendance')

@section('admin_content')
<h4 class="mb-3">Attendance</h4>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Employee</th>
                    <th>Shift</th>
                    <th>Clock in</th>
                    <th>Clock out</th>
                    <th class="text-end">Regular</th>
                    <th class="text-end">Overtime</th>
                    <th class="text-end">Total</th>
                </tr>
            </thead>
            <tbody>
                @forelse($records as $r)
                    <tr>
                        <td>{{ $r->user->first_name }} {{ $r->user->last_name }}</td>
                        <td>{{ $r->shift?->name ?? '-' }}</td>
                        <td>{{ $r->clock_in_at }}</td>
                        <td>{{ $r->clock_out_at ?? 'OPEN' }}</td>
                        <td class="text-end">{{ number_format($r->regular_minutes / 60, 2) }} h</td>
                        <td class="text-end">{{ number_format($r->overtime_minutes / 60, 2) }} h</td>
                        <td class="text-end">{{ number_format($r->total_minutes / 60, 2) }} h</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-4">No attendance yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div class="mt-3">
    {{ $records->links() }}
</div>
@endsection

