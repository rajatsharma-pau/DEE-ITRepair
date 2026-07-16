@extends('layouts.dee')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4>Employees</h4><a class="btn btn-success" href="{{ route('dee.employees.create') }}">Add Employee</a></div>
<table class="table table-bordered table-sm"><thead><tr><th>Name</th><th>Phone</th><th>Designation</th><th>Section</th><th>Role</th><th>Status</th><th>Action</th></tr></thead><tbody>@foreach($employees as $e)<tr><td>{{ $e->name }}</td><td>{{ $e->phone }}</td><td>{{ $e->designation }}</td><td>{{ $e->section }}</td><td>{{ $e->role }}</td><td>{{ $e->status }}</td><td><a class="btn btn-sm btn-primary" href="{{ route('dee.employees.edit',$e->id) }}">Edit</a></td></tr>@endforeach</tbody></table>{{ $employees->links() }}
@endsection
