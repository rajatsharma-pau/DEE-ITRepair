@extends('layouts.app')
@section('content')
<h4>Edit Designation</h4>
@include('masters.partials.alerts')
<div class="alert alert-info">If this designation is already used by employees or service history, please rename it instead of deleting.</div>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('master.designations.update', $designation->id) }}">
    @csrf
    @method('PUT')
    @include('masters.designations.form')
    <button type="submit" class="btn btn-primary">Update</button>
    <a href="{{ route('master.designations.index') }}" class="btn btn-secondary">Back</a>
</form>
</div></div>
@endsection
