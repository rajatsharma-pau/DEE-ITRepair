@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">My Profile</h4>
        <small class="text-muted">View your basic profile and update your profile photo.</small>
    </div>
    <a href="{{ route('home') }}" class="btn btn-sm btn-secondary">Back to Dashboard</a>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <div class="card">
            <div class="card-body text-center">
                @if($employee && $employee->photo_url)
                    <img src="{{ $employee->photo_url }}" alt="Profile Photo" class="rounded-circle mb-3" style="width:130px;height:130px;object-fit:cover;border:4px solid #e9ecef;">
                @else
                    <div class="rounded-circle bg-secondary text-white d-inline-flex align-items-center justify-content-center mb-3" style="width:130px;height:130px;font-size:42px;">
                        {{ strtoupper(substr($user->name ?? 'U', 0, 1)) }}
                    </div>
                @endif

                <h5 class="mb-1">{{ $employee ? $employee->display_name : $user->name }}</h5>
                <div class="text-muted small">{{ $user->roleLabel() }}</div>
                @if($employee)
                    <div class="text-muted small mt-1">{{ optional($employee->department)->name }}</div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-md-8 mb-3">
        <div class="card mb-3">
            <div class="card-header">Update Profile Photo</div>
            <div class="card-body">
                <form method="POST" action="{{ route('profile.photo.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group">
                        <label>Choose photo <span class="text-danger">*</span></label>
                        <input type="file" name="photo" class="form-control-file" accept="image/jpeg,image/png" required>
                        <small class="form-text text-muted">Allowed: JPG, JPEG, PNG. Maximum size: 1 MB.</small>
                        @error('photo')<div class="text-danger small">{{ $message }}</div>@enderror
                    </div>
                    <button class="btn btn-success">Upload Photo</button>
                    @if($employee && $employee->photo)
                        <button type="submit" form="remove-photo-form" class="btn btn-outline-danger" onclick="return confirm('Remove profile photo?')">Remove Photo</button>
                    @endif
                </form>
                @if($employee && $employee->photo)
                    <form id="remove-photo-form" method="POST" action="{{ route('profile.photo.remove') }}" style="display:none;">
                        @csrf
                        @method('DELETE')
                    </form>
                @endif
            </div>
        </div>

        <div class="card">
            <div class="card-header">Basic Details</div>
            <div class="card-body table-responsive">
                <table class="table table-sm table-bordered mb-0">
                    <tr><th style="width:210px;">Name</th><td>{{ $employee ? $employee->display_name : $user->name }}</td></tr>
                    <tr><th>Phone</th><td>{{ $user->phone }}</td></tr>
                    <tr><th>Email</th><td>{{ $user->email ?: '-' }}</td></tr>
                    <tr><th>Designation</th><td>{{ $employee ? ($employee->designation_name ?: '-') : '-' }}</td></tr>
                    <tr><th>College / Directorate</th><td>{{ $employee ? optional($employee->college)->name : optional($user->college)->name }}</td></tr>
                    <tr><th>Department</th><td>{{ $employee ? optional($employee->department)->name : optional($user->department)->name }}</td></tr>
                    <tr><th>Room No.</th><td>{{ $employee && $employee->room_no ? $employee->room_no : '-' }}</td></tr>
                    <tr><th>Job Type</th><td>{{ $employee && $employee->job_type ? $employee->job_type : '-' }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
