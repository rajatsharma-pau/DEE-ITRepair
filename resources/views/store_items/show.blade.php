@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4>{{ $item->name }}</h4><a href="{{ route('store-items.index') }}" class="btn btn-secondary">Back</a></div>
<div class="row">
<div class="col-md-8"><div class="card mb-3"><div class="card-header">Stock Details</div><div class="card-body">
<table class="table table-bordered table-sm">
<tr><th>Code</th><td>{{ $item->item_code }}</td><th>Category</th><td>{{ $item->category }}</td></tr>
<tr><th>Brand</th><td>{{ $item->brand }}</td><th>Unit</th><td>{{ $item->unit }}</td></tr>
<tr><th>Opening Stock</th><td>{{ $item->opening_stock }}</td><th>Current Stock</th><td><strong>{{ $item->current_stock }}</strong></td></tr>
<tr><th>Reorder Level</th><td>{{ $item->reorder_level }}</td><th>Location</th><td>{{ $item->location }}</td></tr>
<tr><th>Description</th><td colspan="3">{{ $item->description }}</td></tr>
</table>
@if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))<a href="{{ route('store-items.edit',$item) }}" class="btn btn-warning">Edit</a>@endif
</div></div></div>
<div class="col-md-4">
@if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))
<div class="card mb-3"><div class="card-header">Stock In / Adjustment</div><div class="card-body"><form method="POST" action="{{ route('store-items.adjust-stock',$item) }}">@csrf
<div class="form-group"><label>Type</label><select name="movement_type" class="form-control"><option>Stock In</option><option>Return</option><option>Adjustment</option></select></div>
<div class="form-group"><label>Quantity</label><input type="number" step="0.01" name="quantity" class="form-control" required></div>
<div class="form-group"><label>Remarks</label><textarea name="remarks" class="form-control" required></textarea></div>
<button class="btn btn-success btn-block">Save Stock</button>
</form></div></div>
@endif
</div>
</div>
<div class="card"><div class="card-header">Stock Movement History</div><div class="card-body table-responsive"><table class="table table-bordered table-sm"><thead><tr><th>Date</th><th>Type</th><th>Qty</th><th>Balance</th><th>Employee</th><th>Remarks</th></tr></thead><tbody>
@foreach($item->stockMovements as $m)<tr><td>{{ optional($m->movement_date)->format('d-m-Y') }}</td><td>{{ $m->movement_type }}</td><td>{{ $m->quantity }}</td><td>{{ $m->balance_after }}</td><td>{{ optional($m->employee)->display_name }}</td><td>{{ $m->remarks }}</td></tr>@endforeach
</tbody></table></div></div>
@endsection
