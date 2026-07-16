@extends('layouts.app')
@section('content')
<div class="card"><div class="card-header">Add Store Item</div><div class="card-body"><form method="POST" action="{{ route('store-items.store') }}">@include('store_items.form')</form></div></div>
@endsection
