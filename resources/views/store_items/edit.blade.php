@extends('layouts.app')
@section('content')
<div class="card"><div class="card-header">Edit Store Item</div><div class="card-body"><form method="POST" action="{{ route('store-items.update',$item) }}">@method('PUT') @include('store_items.form')</form></div></div>
@endsection
