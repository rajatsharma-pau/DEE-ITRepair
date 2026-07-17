@extends('layouts.app')
@section('content')
<h4>Edit College / Directorate</h4>
@include('masters.partials.alerts')
<div class="alert alert-info">If this record is already used by employees, assets, requests or departments, please rename it instead of deleting.</div>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('master.colleges.update', $college->id) }}">
    @csrf
    @method('PUT')
    @include('masters.colleges.form')
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="{{ route('master.colleges.index') }}" class="btn btn-secondary">Back</a>
</form>
</div></div>
@endsection
