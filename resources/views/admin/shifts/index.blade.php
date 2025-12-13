@extends('admin.layout')

@section('title', 'Shifts')

@section('admin_content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Shifts</h4>
    <a class="btn btn-primary btn-sm" href="{{ route('admin.shifts.create') }}">Create shift</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Start</th>
                    <th>End</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($shifts as $shift)
                    <tr>
                        <td>{{ $shift->name }}</td>
                        <td>{{ substr($shift->start_time, 0, 5) }}</td>
                        <td>{{ substr($shift->end_time, 0, 5) }}</td>
                        <td class="text-end">
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('admin.shifts.edit', $shift) }}">Edit</a>
                            <form class="d-inline" method="POST" action="{{ route('admin.shifts.destroy', $shift) }}">
                                @csrf
                                @method('DELETE')
                                <button class="btn btn-outline-danger btn-sm" onclick="return confirm('Delete this shift?')" type="submit">Delete</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-secondary py-4">No shifts yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

