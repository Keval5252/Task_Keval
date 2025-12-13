@extends('admin.layout')

@section('title', 'Employees')

@section('admin_content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h4 class="mb-0">Employees</h4>
    <a class="btn btn-primary btn-sm" href="{{ route('admin.employees.create') }}">Create employee</a>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped mb-0">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                @forelse($employees as $e)
                    <tr>
                        <td>{{ $e->first_name }} {{ $e->last_name }}</td>
                        <td>{{ $e->email }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-secondary py-4">No employees yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

