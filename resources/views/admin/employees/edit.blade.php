@extends('layouts.dee')
@section('content')
<h4>Edit Employee</h4><form method="POST" action="{{ route('dee.employees.update',$employee->id) }}">@csrf @method('PUT') @include('admin.employees.form',['employee'=>$employee])<button class="btn btn-success">Update</button></form>
<hr><h5>Promotion History</h5><form method="POST" action="{{ route('dee.employees.promotions.store',$employee->id) }}">@csrf<div class="form-row"><div class="col"><input name="new_designation" class="form-control" placeholder="New designation" required></div><div class="col"><input type="date" name="promotion_date" class="form-control" required></div><div class="col"><input name="remarks" class="form-control" placeholder="Remarks"></div><div class="col"><button class="btn btn-primary">Add Promotion</button></div></div></form><br>
<table class="table table-bordered table-sm"><tr><th>Old</th><th>New</th><th>Date</th><th>Remarks</th></tr>@foreach($employee->promotions as $p)<tr><td>{{ $p->old_designation }}</td><td>{{ $p->new_designation }}</td><td>{{ $p->promotion_date }}</td><td>{{ $p->remarks }}</td></tr>@endforeach</table>
@endsection
