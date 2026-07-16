@extends('layouts.app')
@section('content')
<div class="card"><div class="card-header">Add Asset</div><div class="card-body"><form method="POST" action="{{ route('assets.store') }}">@include('assets.form')</form></div></div>
@endsection
