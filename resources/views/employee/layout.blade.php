@extends('layouts.app')

@section('title', 'Employee')

@section('content')
<nav class="navbar navbar-expand-lg navbar-dark bg-success">
    <div class="container">
        <a class="navbar-brand" href="{{ route('employee.dashboard') }}">
            Employee
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#employeeNav"
            aria-controls="employeeNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="employeeNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('employee.dashboard') }}">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('employee.attendance.index') }}">Attendance Report</a>
                </li>
            </ul>
            <span class="navbar-text text-white me-3">
                {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
            </span>
            <a class="btn btn-outline-light btn-sm" href="{{ route('employee.logout') }}">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    @yield('employee_content')
</div>
@endsection
