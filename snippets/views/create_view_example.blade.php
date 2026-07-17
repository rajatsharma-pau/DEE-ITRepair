{{-- resources/views/store-items/create.blade.php example --}}
@extends('layouts.app')

@section('content')
<div class="card">
    <div class="card-header">Add Store Item</div>
    <div class="card-body">
        <form method="POST" action="{{ route('store-items.store') }}">
            @csrf
            @include('store-items.form')
        </form>
    </div>
</div>
@endsection
