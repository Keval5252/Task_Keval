<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;

class ShiftController extends Controller
{
    public function index()
    {
        return view('admin.shifts.index', [
            'shifts' => Shift::query()->orderBy('name')->get(),
        ]);
    }

    public function create()
    {
        return view('admin.shifts.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ]);

        Shift::create([
            'name' => $data['name'],
            'start_time' => $data['start_time'] . ':00',
            'end_time' => $data['end_time'] . ':00',
        ]);

        return redirect()->route('admin.shifts.index');
    }

    public function edit(Shift $shift)
    {
        return view('admin.shifts.edit', ['shift' => $shift]);
    }

    public function update(Request $request, Shift $shift)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
        ]);

        $shift->update([
            'name' => $data['name'],
            'start_time' => $data['start_time'] . ':00',
            'end_time' => $data['end_time'] . ':00',
        ]);

        return redirect()->route('admin.shifts.index');
    }

    public function destroy(Shift $shift)
    {
        $shift->delete();
        return redirect()->route('admin.shifts.index');
    }
}

