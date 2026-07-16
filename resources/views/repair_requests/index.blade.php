@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Repair / Material Requests</h4>
    @if(\App\Support\AccessScope::isEmployeeOnly(Auth::user()) || Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))
        <a href="{{ route('repair-requests.create') }}" class="btn btn-success">New Request</a>
    @endif
</div>
<div class="alert alert-info">
    <strong>Workflow:</strong> Employee → Storekeeper → Vendor Estimate → Programmer Technical Verification → Storekeeper prints Financial Sanction → Manual submission to D-4.
</div>

<div class="card mb-3"><div class="card-body">
<form method="GET" class="form-row">
    @if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))
    <div class="col-md-3 mb-2"><select name="college_id" class="form-control"><option value="">All Colleges/Directorates</option>@foreach($colleges ?? [] as $c)<option value="{{ $c->id }}" {{ request('college_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select></div>
    <div class="col-md-3 mb-2"><select name="department_id" class="form-control"><option value="">All Departments</option>@foreach($departments ?? [] as $d)<option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach</select></div>
    @endif
    <div class="col-md-3 mb-2"><input name="status" value="{{ request('status') }}" class="form-control" placeholder="Status"></div>
    <div class="col-md-2 mb-2"><button class="btn btn-primary btn-block">Filter</button></div>
</form>
</div></div>
<div class="card shadow-sm"><div class="card-body table-responsive">
<table class="table table-bordered table-sm">
<thead>
<tr>
    <th>No</th><th>Employee</th><th>Category</th><th>Problem</th><th>Vendor/Estimate</th><th>Status</th><th>Handler</th><th>Assigned</th><th>Action</th>
</tr>
</thead>
<tbody>
@foreach($requests as $r)
<tr>
    <td>{{ $r->request_no }}</td>
    <td>{{ optional($r->employee)->display_name }}</td>
    <td>{{ optional($r->category)->name }}</td>
    <td>{{ str_limit($r->problem_description, 50) }}</td>
    <td>
        @if($r->selectedEstimate)
            {{ optional($r->selectedEstimate->vendor)->name }}<br>
            <small>Rs. {{ number_format($r->selectedEstimate->estimate_amount,2) }}</small>
        @else
            -
        @endif
    </td>
    <td><span class="badge badge-info">{{ $r->status }}</span></td>
    <td>{{ ucwords(str_replace('_',' ', $r->current_handler_role)) }}</td>
    <td>{{ optional($r->assignedTo)->display_name }}</td>
    <td><a class="btn btn-sm btn-info" href="{{ route('repair-requests.show', $r) }}">View</a></td>
</tr>
@endforeach
</tbody>
</table>
{{ $requests->appends(request()->query())->links() }}
</div></div>
@endsection
