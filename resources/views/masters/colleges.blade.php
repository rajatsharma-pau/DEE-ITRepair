@extends('layouts.app')
@section('content')
<h4>Colleges / Directorates Master</h4>
<div class="card mb-3"><div class="card-body">
<form method="POST" action="{{ route('masters.colleges.store') }}" class="row">
@csrf
<div class="col-md-5 form-group"><label>Name</label><input name="name" class="form-control" required></div>
<div class="col-md-3 form-group"><label>Short Name</label><input name="short_name" class="form-control"></div>
<div class="col-md-2 form-group pt-4"><label><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
<div class="col-md-2 form-group pt-4"><button class="btn btn-success">Add</button></div>
</form>
</div></div>
<div class="card"><div class="card-body table-responsive">
<table class="table table-bordered table-sm"><thead><tr><th>ID</th><th>Name</th><th>Short Name</th><th>Active</th></tr></thead><tbody>
@foreach($items as $item)<tr><td>{{ $item->id }}</td><td>{{ $item->name }}</td><td>{{ $item->short_name }}</td><td>{{ $item->is_active ? 'Yes' : 'No' }}</td></tr>@endforeach
</tbody></table>
</div></div>
@endsection
