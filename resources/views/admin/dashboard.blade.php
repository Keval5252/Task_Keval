@extends('admin.layout')

@section('title', 'Admin Dashboard')

@section('admin_content')
<div class="row g-3">
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="text-secondary">Employees</div>
                <div class="fs-3 fw-semibold">{{ $employeesCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="text-secondary">Shifts</div>
                <div class="fs-3 fw-semibold">{{ $shiftsCount }}</div>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card">
            <div class="card-body">
                <div class="text-secondary">Open Attendance</div>
                <div class="fs-3 fw-semibold">{{ $openAttendanceCount }}</div>
            </div>
        </div>
    </div>
</div>
@endsection

