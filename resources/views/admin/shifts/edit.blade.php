@extends('admin.layout')

@section('title', 'Edit Shift')

@section('admin_content')
<h4 class="mb-3">Edit shift</h4>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.shifts.update', $shift) }}">
            @csrf
            @method('PUT')
            <div class="mb-3">
                <label class="form-label">Name</label>
                <input class="form-control" name="name" value="{{ old('name', $shift->name) }}" required>
                @error('name') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Start time</label>
                    <input class="form-control" type="time" name="start_time"
                        value="{{ old('start_time', substr($shift->start_time, 0, 5)) }}" required>
                    @error('start_time') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">End time</label>
                    <input class="form-control" type="time" name="end_time"
                        value="{{ old('end_time', substr($shift->end_time, 0, 5)) }}" required>
                    @error('end_time') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">Save</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.shifts.index') }}">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

