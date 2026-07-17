@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Department / Office / KVK Master</h4>
    <a href="{{ route('master.departments.create') }}" class="btn btn-success btn-sm">Add Department</a>
</div>

@include('masters.partials.alerts')

<div class="card mb-3"><div class="card-body">
<form method="GET" action="{{ route('master.departments.index') }}" class="row">
    <div class="col-md-3 form-group">
        <label>Search</label>
        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Name / Place / College">
    </div>
    <div class="col-md-4 form-group">
        <label>College / Directorate</label>
        <select name="college_id" class="form-control">
            <option value="">All</option>
            @foreach($colleges as $college)
                <option value="{{ $college->id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>{{ $college->name }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 form-group">
        <label>Status</label>
        <select name="status" class="form-control">
            <option value="">All</option>
            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
        </select>
    </div>
    <div class="col-md-3 form-group pt-4">
        <button type="submit" class="btn btn-primary btn-sm">Search</button>
        <a href="{{ route('master.departments.index') }}" class="btn btn-secondary btn-sm">Reset</a>
    </div>
</form>
</div></div>

<div class="card"><div class="card-body table-responsive">
<table class="table table-bordered table-sm">
    <thead><tr><th>College / Directorate</th><th>Department / Office / KVK</th><th>Place</th><th>Status</th><th width="170">Action</th></tr></thead>
    <tbody>
    @forelse($departments as $department)
        <tr>
            <td>{{ optional($department->college)->name }}</td>
            <td>{{ $department->name }}</td>
            <td>{{ $department->place }}</td>
            <td>
                @if(isset($department->is_active) && !$department->is_active)
                    <span class="badge badge-secondary">Inactive</span>
                @else
                    <span class="badge badge-success">Active</span>
                @endif
            </td>
            <td>
                <a href="{{ route('master.departments.edit', $department->id) }}" class="btn btn-primary btn-sm">Edit</a>
                <form action="{{ route('master.departments.destroy', $department->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete/deactivate this record? If it is being used, it will be deactivated only.');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                </form>
            </td>
        </tr>
    @empty
        <tr><td colspan="5" class="text-center text-muted">No record found.</td></tr>
    @endforelse
    </tbody>
</table>
{{ $departments->links() }}
</div></div>
@endsection
