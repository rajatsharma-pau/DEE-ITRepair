@extends('layouts.app')
@section('content')
<h4>Section Master</h4>
<div class="card mb-3"><div class="card-body"><form method="POST" action="{{ route('masters.sections.store') }}">@csrf<div class="row"><div class="col-md-4"><select name="directorate_id" class="form-control" required>@foreach($directorates as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></div><div class="col-md-3"><input name="name" class="form-control" placeholder="Section" required></div><div class="col-md-2"><input name="short_name" class="form-control" placeholder="Short"></div><div class="col-md-1"><label><input type="checkbox" name="is_active" checked> Active</label></div><div class="col-md-2"><button class="btn btn-success">Add</button></div></div></form></div></div>
<table class="table table-bordered table-sm"><thead><tr><th>Directorate</th><th>Section</th><th>Short</th><th>Active</th></tr></thead><tbody>@foreach($items as $i)<tr><td>{{ optional($i->directorate)->short_name }}</td><td>{{ $i->name }}</td><td>{{ $i->short_name }}</td><td>{{ $i->is_active?'Yes':'No' }}</td></tr>@endforeach</tbody></table>
@endsection
