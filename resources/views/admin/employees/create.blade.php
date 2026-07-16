@extends('layouts.dee')
@section('content')
<h4>Add Employee</h4><form method="POST" action="{{ route('dee.employees.store') }}">@csrf @include('admin.employees.form',['employee'=>null])<button class="btn btn-success">Save</button></form>
@endsection
