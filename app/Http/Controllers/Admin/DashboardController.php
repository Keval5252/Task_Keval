<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use App\Models\Shift;
use App\Models\User;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'employeesCount' => User::query()->where('role', 'employee')->count(),
            'shiftsCount' => Shift::query()->count(),
            'openAttendanceCount' => AttendanceRecord::query()->whereNull('clock_out_at')->count(),
        ]);
    }
}

