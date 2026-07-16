@extends('layouts.app')
@section('content')
<h4>Default Problem / Material Requirement Master</h4>
<div class="card mb-3"><div class="card-body">
<form method="POST" action="{{ route('masters.problem-templates.store') }}">
@csrf
<div class="row">
    <div class="col-md-3 form-group">
        <label>Category</label>
        <select name="repair_category_id" class="form-control">
            <option value="">General / Any Category</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}">{{ $c->name }} - {{ $c->item_group }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2 form-group">
        <label>Group</label>
        <select name="item_group" class="form-control">
            <option value="">Auto from category</option>
            <option>Computer Related</option>
            <option>Non Computer</option>
            <option>General</option>
        </select>
    </div>
    <div class="col-md-3 form-group">
        <label class="required">Title</label>
        <input name="title" class="form-control" placeholder="Printer not printing" required>
    </div>
    <div class="col-md-3 form-group">
        <label>Description</label>
        <input name="description" class="form-control" placeholder="Detailed default text">
    </div>
    <div class="col-md-1 form-group pt-4"><label><input type="checkbox" name="is_active" checked> Active</label></div>
</div>
<button class="btn btn-success">Add Default Problem</button>
</form>
</div></div>

<div class="card"><div class="card-body table-responsive">
<table class="table table-bordered table-sm">
<thead><tr><th>Title</th><th>Category</th><th>Group</th><th>Description</th><th>Active</th></tr></thead>
<tbody>
@foreach($items as $i)
<tr>
    <td>{{ $i->title }}</td>
    <td>{{ optional($i->category)->name ?: 'Any' }}</td>
    <td>{{ $i->item_group }}</td>
    <td>{{ $i->description }}</td>
    <td>{{ $i->is_active ? 'Yes' : 'No' }}</td>
</tr>
@endforeach
</tbody>
</table>
</div></div>
@endsection
