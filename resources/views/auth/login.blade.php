@extends('layouts.app')
@section('content')
<div class="row justify-content-center">
    <div class="col-md-5">
        <div class="card shadow-sm">
            <div class="card-header text-center">DEE Employee & IT Repair Management System</div>
            <div class="card-body">
                <form method="POST" action="{{ route('login') }}">
                    @csrf
                    <div class="form-group">
                        <label class="required">Phone Number</label>
                        <input type="text" name="phone" value="{{ old('phone') }}" class="form-control" required autofocus>
                    </div>
                    <div class="form-group">
                        <label class="required">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="form-group form-check">
                        <input type="checkbox" name="remember" class="form-check-input" id="remember">
                        <label class="form-check-label" for="remember">Remember me</label>
                    </div>
                    <button class="btn btn-success btn-block">Login</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
