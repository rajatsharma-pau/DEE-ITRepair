@extends('layouts.app')
@section('content')
<h4>Add Employee</h4>
<form method="POST" action="{{ route('employees.store') }}" enctype="multipart/form-data">
    @csrf
    @include('employees.form')
    <button class="btn btn-success">Save Employee</button>
    <a href="{{ route('employees.index') }}" class="btn btn-secondary">Cancel</a>
</form>
@endsection
