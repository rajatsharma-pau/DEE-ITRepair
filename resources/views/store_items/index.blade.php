@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>Store Inventory</h4>
    @if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))<a href="{{ route('store-items.create') }}" class="btn btn-success">Add Store Item</a>@endif
</div>
<div class="card mb-3"><div class="card-body"><form method="GET" class="form-row">
<div class="col-md-3 mb-2"><input name="q" value="{{ request('q') }}" class="form-control" placeholder="Search paper, pen, stationery item"></div>
@if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))
<div class="col-md-3 mb-2"><select name="college_id" class="form-control"><option value="">All Colleges/Directorates</option>@foreach($colleges ?? [] as $c)<option value="{{ $c->id }}" {{ request('college_id')==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select></div>
<div class="col-md-3 mb-2"><select name="department_id" class="form-control"><option value="">All Departments</option>@foreach($departments ?? [] as $d)<option value="{{ $d->id }}" {{ request('department_id')==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach</select></div>
@endif
<div class="col-md-2 mb-2"><label><input type="checkbox" name="low_stock" value="1" {{ request('low_stock')?'checked':'' }}> Low stock only</label></div>
<div class="col-md-1 mb-2"><button class="btn btn-primary btn-block">Go</button></div>
</form></div></div>
<div class="card"><div class="card-body table-responsive"><table class="table table-bordered table-sm">
<thead><tr><th>Code</th><th>Item</th><th>Category</th><th>Unit</th><th>Current Stock</th><th>Reorder Level</th><th>Status</th><th>Action</th></tr></thead><tbody>
@forelse($items as $item)
<tr>
<td>{{ $item->item_code }}</td><td>{{ $item->name }}<br><small>{{ $item->brand }}</small></td><td>{{ $item->category }}</td><td>{{ $item->unit }}</td>
<td><strong>{{ $item->current_stock }}</strong></td><td>{{ $item->reorder_level }}</td>
<td>@if($item->current_stock <= $item->reorder_level)<span class="badge badge-danger">Low Stock</span>@else<span class="badge badge-success">OK</span>@endif</td>
<td><a href="{{ route('store-items.show',$item) }}" class="btn btn-sm btn-primary">View</a> @if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))<a href="{{ route('store-items.edit',$item) }}" class="btn btn-sm btn-warning">Edit</a>@endif</td>
</tr>
@empty <tr><td colspan="8">No store item found.</td></tr> @endforelse
</tbody></table>{{ $items->appends(request()->query())->links() }}</div></div>
@endsection
