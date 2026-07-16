@extends('layouts.app')
@section('content')
<h4>Departments / Offices / KVKs Master</h4>
<div class="card mb-3"><div class="card-body">
<form method="POST" action="{{ route('masters.departments.store') }}" class="row">
@csrf
<div class="col-md-4 form-group"><label>College / Directorate</label><select name="college_id" class="form-control" required><option value="">Select</option>@foreach($colleges as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
<div class="col-md-4 form-group"><label>Department / Office / KVK Name</label><input name="name" class="form-control" required></div>
<div class="col-md-2 form-group"><label>Place</label><input name="place" class="form-control"></div>
<div class="col-md-1 form-group pt-4"><label><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
<div class="col-md-1 form-group pt-4"><button class="btn btn-success">Add</button></div>
</form>
</div></div>
<div class="card"><div class="card-body table-responsive">
<table class="table table-bordered table-sm"><thead><tr><th>ID</th><th>College / Directorate</th><th>Name</th><th>Place</th><th>Active</th></tr></thead><tbody>
@foreach($items as $item)<tr><td>{{ $item->id }}</td><td>{{ optional($item->college)->name }}</td><td>{{ $item->name }}</td><td>{{ $item->place }}</td><td>{{ $item->is_active ? 'Yes' : 'No' }}</td></tr>@endforeach
</tbody></table>
</div></div>
@endsection
