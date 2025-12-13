@extends('admin.layout')

@section('title', 'Assign Shift')

@section('admin_content')
<h4 class="mb-3">Assign shift to employee</h4>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.assignments.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Employee</label>
                    <select class="form-select" name="user_id" required>
                        <option value="">Select employee</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}" @selected(old('user_id') == $e->id)>
                                {{ $e->first_name }} {{ $e->last_name }} ({{ $e->email }})
                            </option>
                        @endforeach
                    </select>
                    @error('user_id') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Shift</label>
                    <select class="form-select" name="shift_id" required>
                        <option value="">Select shift</option>
                        @foreach($shifts as $s)
                            <option value="{{ $s->id }}" @selected(old('shift_id') == $s->id)>
                                {{ $s->name }} ({{ substr($s->start_time,0,5) }} - {{ substr($s->end_time,0,5) }})
                            </option>
                        @endforeach
                    </select>
                    @error('shift_id') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Effective from</label>
                    <input class="form-control" type="date" name="effective_from" value="{{ old('effective_from', date('Y-m-d')) }}" required>
                    @error('effective_from') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Effective to (optional)</label>
                    <input class="form-control" type="date" name="effective_to" value="{{ old('effective_to') }}">
                    @error('effective_to') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">Save</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.dashboard') }}">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

