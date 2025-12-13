@extends('admin.layout')

@section('title', 'Create Employee')

@section('admin_content')
<h4 class="mb-3">Create employee</h4>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('admin.employees.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">First name</label>
                    <input class="form-control" name="first_name" value="{{ old('first_name') }}" required>
                    @error('first_name') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Last name</label>
                    <input class="form-control" name="last_name" value="{{ old('last_name') }}" required>
                    @error('last_name') <div class="text-danger small">{{ $message }}</div> @enderror
                </div>
            </div>
            <div class="mb-3">
                <label class="form-label">Email</label>
                <input class="form-control" type="email" name="email" value="{{ old('email') }}" required>
                @error('email') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="mb-3">
                <label class="form-label">Password (for mobile login)</label>
                <input class="form-control" type="password" name="password" required>
                @error('password') <div class="text-danger small">{{ $message }}</div> @enderror
            </div>
            <div class="d-flex gap-2">
                <button class="btn btn-primary" type="submit">Save</button>
                <a class="btn btn-outline-secondary" href="{{ route('admin.employees.index') }}">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

