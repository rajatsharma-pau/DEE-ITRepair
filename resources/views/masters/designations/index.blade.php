@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Designation Master</h4>
    <a href="{{ route('master.designations.create') }}" class="btn btn-success btn-sm">Add Designation</a>
</div>

@include('masters.partials.alerts')

<div class="card mb-3"><div class="card-body">
<form method="GET" action="{{ route('master.designations.index') }}" class="row">
    <div class="col-md-4 form-group">
        <label>Search</label>
        <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Designation / Cadre">
    </div>
    <div class="col-md-3 form-group">
        <label>Cadre</label>
        <select name="cadre" class="form-control">
            <option value="">All</option>
            @foreach($cadres as $cadre)
                <option value="{{ $cadre }}" {{ request('cadre') == $cadre ? 'selected' : '' }}>{{ $cadre }}</option>
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
        <a href="{{ route('master.designations.index') }}" class="btn btn-secondary btn-sm">Reset</a>
    </div>
</form>
</div></div>

<div class="card"><div class="card-body table-responsive">
<table class="table table-bordered table-sm">
    <thead><tr><th>Name</th><th>Cadre</th><th>Sort Order</th><th>Status</th><th width="170">Action</th></tr></thead>
    <tbody>
    @forelse($designations as $designation)
        <tr>
            <td>{{ $designation->name }}</td>
            <td>{{ $designation->cadre }}</td>
            <td>{{ $designation->sort_order }}</td>
            <td>
                @if(isset($designation->is_active) && !$designation->is_active)
                    <span class="badge badge-secondary">Inactive</span>
                @else
                    <span class="badge badge-success">Active</span>
                @endif
            </td>
            <td>
                <a href="{{ route('master.designations.edit', $designation->id) }}" class="btn btn-primary btn-sm">Edit</a>
                <form action="{{ route('master.designations.destroy', $designation->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete/deactivate this designation? If it is being used, it will be deactivated only.');">
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
{{ $designations->links() }}
</div></div>
@endsection
