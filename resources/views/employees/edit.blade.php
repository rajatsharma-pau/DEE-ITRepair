@extends('layouts.app')
@section('content')
<h4>Edit Employee</h4>
<form method="POST" action="{{ route('employees.update', $employee) }}" enctype="multipart/form-data">
    @csrf @method('PUT')
    @include('employees.form')
    <button class="btn btn-success">Update Employee</button>
    <a href="{{ route('employees.show', $employee) }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
