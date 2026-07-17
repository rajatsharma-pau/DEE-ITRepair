@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">Forgot Password</div>
                <div class="card-body">
                    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif

                    <form method="POST" action="{{ route('password.otp.send') }}">
                        @csrf
                        <div class="form-group">
                            <label>Registered Mobile Number</label>
                            <input type="text" name="phone" value="{{ old('phone') }}" class="form-control @error('phone') is-invalid @enderror" required autofocus>
                            @error('phone')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary">Send OTP</button>
                        <a href="{{ route('login') }}" class="btn btn-link">Back to Login</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
