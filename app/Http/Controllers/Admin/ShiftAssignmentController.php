<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use App\Models\ShiftAssignment;
use App\Models\User;
use Illuminate\Http\Request;

class ShiftAssignmentController extends Controller
{
    public function create()
    {
        return view('admin.assignments.create', [
            'employees' => User::query()->where('role', 'employee')->orderBy('first_name')->get(),
            'shifts' => Shift::query()->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
            'shift_id' => ['required', 'exists:shifts,id'],
            'effective_from' => ['required', 'date'],
            'effective_to' => ['nullable', 'date', 'after_or_equal:effective_from'],
        ]);

        ShiftAssignment::create($data);

        return redirect()->route('admin.employees.index');
    }
}

