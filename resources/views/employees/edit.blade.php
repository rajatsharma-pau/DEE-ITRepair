@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Edit Employee</h4>
        <div class="text-muted small">Update official service details, login access and role permissions.</div>
    </div>
    <div>
        <a href="{{ route('employees.show', $employee) }}" class="btn btn-outline-secondary btn-sm">Back to Profile</a>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm">Employee List</a>
    </div>
</div>

<form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data">
    @csrf
    @method('PUT')
    @include('employees.form')

    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Please verify mandatory fields before updating. Password remains unchanged if left blank.
            </div>
            <div>
                <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">Update Employee</button>
            </div>
        </div>
    </div>
</form>
@endsection
