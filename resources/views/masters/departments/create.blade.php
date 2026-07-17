@extends('layouts.app')
@section('content')
<h4>Add Department / Office / KVK</h4>
@include('masters.partials.alerts')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('master.departments.store') }}">
    @csrf
    @include('masters.departments.form')
    <button type="submit" class="btn btn-success">Save</button>
    <a href="{{ route('master.departments.index') }}" class="btn btn-secondary">Back</a>
</form>
</div></div>
@endsection
