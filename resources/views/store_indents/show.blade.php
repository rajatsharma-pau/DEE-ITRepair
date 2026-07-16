@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3"><h4>Indent {{ $indent->indent_no }}</h4><a href="{{ route('store-indents.index') }}" class="btn btn-secondary">Back</a></div>
<div class="card mb-3"><div class="card-body">
<table class="table table-bordered table-sm">
<tr><th>Employee</th><td>{{ optional($indent->employee)->display_name }}</td><th>Status</th><td><span class="badge badge-info">{{ $indent->status }}</span></td></tr>
<tr><th>Required Date</th><td>{{ optional($indent->required_date)->format('d-m-Y') }}</td><th>Issued Date</th><td>{{ optional($indent->issued_date)->format('d-m-Y') }}</td></tr>
<tr><th>Employee Remarks</th><td colspan="3">{{ $indent->employee_remarks }}</td></tr>
<tr><th>Storekeeper Remarks</th><td colspan="3">{{ $indent->storekeeper_remarks }}</td></tr>
</table>
</div></div>
<div class="card mb-3"><div class="card-header">Indent Items</div><div class="card-body">
@if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']) && $indent->status == 'Submitted')
<form method="POST" action="{{ route('store-indents.issue',$indent) }}">@csrf
@endif
<table class="table table-bordered table-sm"><thead><tr><th>Item</th><th>Available Stock</th><th>Requested</th><th>Issued Qty</th></tr></thead><tbody>
@foreach($indent->items as $line)
<tr><td>{{ optional($line->storeItem)->name }}</td><td>{{ optional($line->storeItem)->current_stock }} {{ optional($line->storeItem)->unit }}</td><td>{{ $line->requested_qty }}</td><td>
@if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']) && $indent->status == 'Submitted')
<input type="number" step="0.01" name="issued_qty[{{ $line->id }}]" class="form-control" value="{{ $line->requested_qty }}">
@else {{ $line->issued_qty }} @endif
</td></tr>
@endforeach
</tbody></table>
@if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']) && $indent->status == 'Submitted')
<div class="form-group"><label>Storekeeper Remarks</label><textarea name="storekeeper_remarks" class="form-control"></textarea></div>
<button class="btn btn-success">Issue Items and Decrement Stock</button>
</form>
<form method="POST" action="{{ route('store-indents.reject',$indent) }}" class="mt-2">@csrf
<div class="input-group"><input name="storekeeper_remarks" class="form-control" placeholder="Reason for rejection" required><div class="input-group-append"><button class="btn btn-danger">Reject</button></div></div>
</form>
@endif
</div></div>
<div class="card"><div class="card-header">Stock Movements Created</div><div class="card-body table-responsive"><table class="table table-bordered table-sm"><thead><tr><th>Item</th><th>Qty</th><th>Balance</th><th>Remarks</th></tr></thead><tbody>
@foreach($indent->stockMovements as $m)<tr><td>{{ optional($m->storeItem)->name }}</td><td>{{ $m->quantity }}</td><td>{{ $m->balance_after }}</td><td>{{ $m->remarks }}</td></tr>@endforeach
</tbody></table></div></div>
@endsection
