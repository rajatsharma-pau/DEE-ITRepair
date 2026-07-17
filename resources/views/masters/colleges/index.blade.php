@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>College / Directorate Master</h4>
    <a href="{{ route('master.colleges.create') }}" class="btn btn-success btn-sm">Add College / Directorate</a>
</div>

@include('masters.partials.alerts')

<div class="card mb-3">
    <div class="card-body">
        <form method="GET" action="{{ route('master.colleges.index') }}" class="row">
            <div class="col-md-5 form-group">
                <label>Search</label>
                <input type="text" name="search" class="form-control" value="{{ request('search') }}" placeholder="Search by name">
            </div>
            <div class="col-md-3 form-group">
                <label>Status</label>
                <select name="status" class="form-control">
                    <option value="">All</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="col-md-4 form-group pt-4">
                <button type="submit" class="btn btn-primary btn-sm">Search</button>
                <a href="{{ route('master.colleges.index') }}" class="btn btn-secondary btn-sm">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card">
    <div class="card-body table-responsive">
        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Short Name</th>
                    <th>Status</th>
                    <th width="170">Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($colleges as $college)
                <tr>
                    <td>{{ $college->name }}</td>
                    <td>{{ $college->short_name ?? '-' }}</td>
                    <td>
                        @if(isset($college->is_active) && !$college->is_active)
                            <span class="badge badge-secondary">Inactive</span>
                        @else
                            <span class="badge badge-success">Active</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('master.colleges.edit', $college->id) }}" class="btn btn-primary btn-sm">Edit</a>
                        <form action="{{ route('master.colleges.destroy', $college->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete/deactivate this record? If it is being used, it will be deactivated only.');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr><td colspan="4" class="text-center text-muted">No record found.</td></tr>
            @endforelse
            </tbody>
        </table>
        {{ $colleges->links() }}
    </div>
</div>
@endsection
