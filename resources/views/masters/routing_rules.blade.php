@extends('layouts.app')
@section('content')
<h4>Repair Routing Rules</h4>
<div class="card mb-3"><div class="card-body"><form method="POST" action="{{ route('masters.routing-rules.store') }}">@csrf<div class="row">
<div class="col-md-3"><label>Category</label><select name="repair_category_id" class="form-control">@foreach($categories as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
<div class="col-md-2"><label>Handler Type</label><select name="handler_type" class="form-control"><option value="role">Role</option><option value="charge">Charge</option><option value="employee">Employee</option></select></div>
<div class="col-md-3"><label>Handler Value</label><input name="handler_value" class="form-control" placeholder="programmer / storekeeper / Store Incharge"></div>
<div class="col-md-4"><label>Specific Employee</label><select name="handler_employee_id" class="form-control"><option value="">None</option>@foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->display_name }}</option>@endforeach</select></div>
<div class="col-md-3"><label><input type="checkbox" name="requires_store_verification"> Store Verification</label></div>
<div class="col-md-3"><label><input type="checkbox" name="requires_store_incharge_approval"> Store Incharge Approval</label></div>
<div class="col-md-3"><label><input type="checkbox" name="requires_programmer_verification"> Programmer Verification</label></div>
<div class="col-md-1"><label><input type="checkbox" name="is_active" checked> Active</label></div>
<div class="col-md-2"><button class="btn btn-success">Add Rule</button></div>
</div></form></div></div>
<table class="table table-bordered table-sm"><thead><tr><th>Category</th><th>Type</th><th>Value</th><th>Employee</th><th>Checks</th><th>Active</th></tr></thead><tbody>@foreach($items as $i)<tr><td>{{ optional($i->category)->name }}</td><td>{{ $i->handler_type }}</td><td>{{ $i->handler_value }}</td><td>{{ optional($i->handlerEmployee)->display_name }}</td><td>Store: {{ $i->requires_store_verification?'Y':'N' }}, Incharge: {{ $i->requires_store_incharge_approval?'Y':'N' }}, Programmer: {{ $i->requires_programmer_verification?'Y':'N' }}</td><td>{{ $i->is_active?'Yes':'No' }}</td></tr>@endforeach</tbody></table>
@endsection
