@extends('layouts.app')

@section('title', 'Admin')

@section('content')
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="{{ route('admin.dashboard') }}">
            Admin
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNav"
            aria-controls="adminNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="adminNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.shifts.index') }}">Shifts</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.employees.index') }}">Employees</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.assignments.create') }}">Assign Shift</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="{{ route('admin.attendance.index') }}">Attendance</a>
                </li>
            </ul>
            <span class="navbar-text text-white me-3">
                {{ auth()->user()->first_name }} {{ auth()->user()->last_name }}
            </span>
            <a class="btn btn-outline-light btn-sm" href="{{ url('logout') }}">Logout</a>
        </div>
    </div>
</nav>

<div class="container py-4">
    @yield('admin_content')
</div>
@endsection

