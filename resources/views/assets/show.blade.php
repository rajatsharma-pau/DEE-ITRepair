@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4>Asset Detail</h4><a href="{{ route('assets.index') }}" class="btn btn-secondary">Back</a></div>
<div class="row">
<div class="col-md-8"><div class="card mb-3"><div class="card-header">{{ $asset->item_name }}</div><div class="card-body">
<table class="table table-bordered table-sm">
<tr><th>Asset Code</th><td>{{ $asset->asset_code }}</td><th>Inventory No</th><td>{{ $asset->inventory_no }}</td></tr>
<tr><th>Category</th><td>{{ $asset->asset_category }}</td><th>Make / Model</th><td>{{ $asset->make }} {{ $asset->model }}</td></tr>
<tr><th>Serial No</th><td>{{ $asset->serial_no }}</td><th>Configuration</th><td>{{ $asset->configuration }}</td></tr>
<tr><th>State</th><td><span class="badge badge-info">{{ $asset->asset_state }}</span></td><th>Assigned To</th><td>{{ optional($asset->assignedTo)->display_name ?: '-' }}</td></tr>
<tr><th>Condition</th><td>{{ $asset->condition_status }}</td><th>Location</th><td>{{ $asset->location }}</td></tr>
<tr><th>Purchase</th><td>{{ optional($asset->purchase_date)->format('d-m-Y') }} / Rs. {{ $asset->purchase_amount }}</td><th>Warranty</th><td>{{ optional($asset->warranty_till)->format('d-m-Y') }}</td></tr>
<tr><th>Remarks</th><td colspan="3">{{ $asset->remarks }}</td></tr>
</table>
@if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))<a href="{{ route('assets.edit',$asset) }}" class="btn btn-warning">Edit</a>@endif
</div></div></div>
<div class="col-md-4">
@if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))
<div class="card mb-3"><div class="card-header">Add Asset Movement</div><div class="card-body">
<form method="POST" action="{{ route('assets.history.store',$asset) }}">@csrf
<div class="form-group"><label>New State</label><select name="asset_state" class="form-control" required>@foreach($states as $s)<option value="{{ $s }}">{{ $s }}</option>@endforeach</select></div>
<div class="form-group"><label>Employee if With Employee</label><select name="assigned_to_employee_id" class="form-control"><option value="">-- Select --</option>@foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->display_name }}</option>@endforeach</select></div>
<div class="form-group"><label>Date</label><input type="date" name="action_date" class="form-control" value="{{ date('Y-m-d') }}"></div>
<div class="form-group"><label>Remarks</label><textarea name="remarks" class="form-control"></textarea></div>
<button class="btn btn-success btn-block">Save Movement</button>
</form>
</div></div>
@endif
</div>
</div>
<div class="card"><div class="card-header">Asset History</div><div class="card-body table-responsive"><table class="table table-bordered table-sm"><thead><tr><th>Date</th><th>Action</th><th>From</th><th>To</th><th>Employee</th><th>Remarks</th></tr></thead><tbody>
@foreach($asset->histories as $h)<tr><td>{{ optional($h->action_date)->format('d-m-Y') }}</td><td>{{ $h->action_type }}</td><td>{{ $h->from_state }}</td><td>{{ $h->to_state }}</td><td>{{ optional($h->employee)->display_name }}</td><td>{{ $h->remarks }}</td></tr>@endforeach
</tbody></table></div></div>
@endsection
