@extends('layouts.app')
@section('content')
<div class="card"><div class="card-header">Edit Asset</div><div class="card-body"><form method="POST" action="{{ route('assets.update',$asset) }}">@method('PUT') @include('assets.form')</form></div></div>
@endsection
