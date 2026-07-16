@extends('layouts.app')
@section('content')
<h4>Designation Master</h4>
<div class="card mb-3"><div class="card-body"><form method="POST" action="{{ route('masters.designations.store') }}">@csrf<div class="row"><div class="col-md-4"><input name="name" class="form-control" placeholder="Designation" required></div><div class="col-md-3"><input name="cadre" class="form-control" placeholder="Cadre"></div><div class="col-md-2"><input name="sort_order" class="form-control" placeholder="Order" value="0"></div><div class="col-md-1"><label><input type="checkbox" name="is_active" checked> Active</label></div><div class="col-md-2"><button class="btn btn-success">Add</button></div></div></form></div></div>
<table class="table table-bordered table-sm"><thead><tr><th>Name</th><th>Cadre</th><th>Order</th><th>Active</th></tr></thead><tbody>@foreach($items as $i)<tr><td>{{ $i->name }}</td><td>{{ $i->cadre }}</td><td>{{ $i->sort_order }}</td><td>{{ $i->is_active?'Yes':'No' }}</td></tr>@endforeach</tbody></table>
@endsection
