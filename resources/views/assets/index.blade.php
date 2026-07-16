@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>{{ \App\Support\AccessScope::isEmployeeOnly(Auth::user()) ? 'My Allocated Assets' : 'Asset Management' }}</h4>
    @if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))
        <a href="{{ route('assets.create') }}" class="btn btn-success">Add Asset</a>
    @endif
</div>

@if(\App\Support\AccessScope::isEmployeeOnly(Auth::user()))
<div class="alert alert-info">Only assets currently allocated to you are shown here.</div>
@endif

<div class="card mb-3"><div class="card-body">
    <form method="GET" class="form-row">
        <div class="col-md-3 mb-2"><input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search asset / inventory / serial no"></div>
        <div class="col-md-2 mb-2"><select name="asset_category" class="form-control"><option value="">All Categories</option>@foreach($categories as $c)<option value="{{ $c }}" {{ request('asset_category')==$c?'selected':'' }}>{{ $c }}</option>@endforeach</select></div>
        <div class="col-md-2 mb-2"><select name="asset_state" class="form-control"><option value="">All States</option>@foreach($states as $s)<option value="{{ $s }}" {{ request('asset_state')==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
        @if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))
        <div class="col-md-2 mb-2"><select name="college_id" class="form-control"><option value="">All Colleges</option>@foreach($colleges ?? [] as $c)<option value="{{ $c->id }}" {{ request('college_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select></div>
        <div class="col-md-2 mb-2"><select name="department_id" class="form-control"><option value="">All Departments</option>@foreach($departments ?? [] as $d)<option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach</select></div>
        <div class="col-md-3 mb-2"><select name="employee_id" class="form-control"><option value="">All Employees</option>@foreach($employees as $e)<option value="{{ $e->id }}" {{ request('employee_id')==$e->id?'selected':'' }}>{{ $e->display_name }}</option>@endforeach</select></div>
        @endif
        <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Filter</button></div>
    </form>
</div></div>
<div class="card"><div class="card-body table-responsive">
<table class="table table-bordered table-sm">
<thead><tr><th>Asset</th><th>Inventory No</th><th>Category</th><th>Make/Model</th><th>State</th><th>Assigned To</th><th>Department</th><th>Condition</th><th>Action</th></tr></thead>
<tbody>
@forelse($assets as $a)
<tr>
<td>{{ $a->item_name }}<br><small>{{ $a->asset_code }}</small></td>
<td>{{ $a->inventory_no }}</td>
<td>{{ $a->asset_category }}</td>
<td>{{ $a->make }} {{ $a->model }}<br><small>{{ $a->serial_no }}</small></td>
<td><span class="badge badge-info">{{ $a->asset_state }}</span></td>
<td>{{ optional($a->assignedTo)->display_name ?: '-' }}</td>
<td>{{ optional($a->department)->name ?: '-' }}</td>
<td>{{ $a->condition_status }}</td>
<td><a href="{{ route('assets.show',$a) }}" class="btn btn-sm btn-primary">View</a> @if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))<a href="{{ route('assets.edit',$a) }}" class="btn btn-sm btn-warning">Edit</a>@endif</td>
</tr>
@empty <tr><td colspan="9">No asset found.</td></tr> @endforelse
</tbody>
</table>
{{ $assets->appends(request()->query())->links() }}
</div></div>
@endsection
