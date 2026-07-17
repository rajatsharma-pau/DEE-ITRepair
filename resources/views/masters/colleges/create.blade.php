@extends('layouts.app')
@section('content')
<h4>Add College / Directorate</h4>
@include('masters.partials.alerts')
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('master.colleges.store') }}">
    @csrf
    @include('masters.colleges.form')
    <button type="submit" class="btn btn-success">Save</button>
    <a href="{{ route('master.colleges.index') }}" class="btn btn-secondary">Back</a>
</form>
</div></div>
@endsection
