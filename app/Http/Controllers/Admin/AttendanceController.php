<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AttendanceRecord;
use Illuminate\Http\Request;

class AttendanceController extends Controller
{
    public function index(Request $request)
    {
        $query = AttendanceRecord::query()
            ->with(['user', 'shift'])
            ->latest('clock_in_at');

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        return view('admin.attendance.index', [
            'records' => $query->paginate(50)->withQueryString(),
        ]);
    }
}

