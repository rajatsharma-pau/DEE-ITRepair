@extends('layouts.app')
@section('content')
<h4>Add Designation</h4>
@include('masters.partials.alerts')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('master.designations.store') }}">
    @csrf
    @include('masters.designations.form')
    <button type="submit" class="btn btn-success">Save</button>
    <a href="{{ route('master.designations.index') }}" class="btn btn-secondary">Back</a>
</form>
</div></div>
@endsection
