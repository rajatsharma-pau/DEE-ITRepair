@extends('layouts.app')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Add Employee</h4>
        <div class="text-muted small">Create employee login, official posting and role permissions.</div>
    </div>
    <div>
        <a href="{{ route('employees.index') }}" class="btn btn-outline-secondary btn-sm">Employee List</a>
    </div>
</div>

<form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
    @csrf
    @include('employees.form')

    <div class="card mb-4">
        <div class="card-body d-flex justify-content-between align-items-center">
            <div class="text-muted small">
                Mandatory fields are marked with red *. Department Admin can add employees only in own department.
            </div>
            <div>
                <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
                <button type="submit" class="btn btn-success">Save Employee</button>
            </div>
        </div>
    </div>
</form>
@endsection
