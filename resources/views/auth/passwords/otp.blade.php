@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header">Reset Password by OTP</div>
                <div class="card-body">
                    @if(session('success')) <div class="alert alert-success">{{ session('success') }}</div> @endif
                    <p class="text-muted">OTP sent to mobile number: <strong>{{ $phone }}</strong></p>

                    <form method="POST" action="{{ route('password.otp.reset') }}">
                        @csrf
                        <div class="form-group">
                            <label>OTP</label>
                            <input type="text" name="otp" maxlength="6" class="form-control @error('otp') is-invalid @enderror" required autofocus>
                            @error('otp')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label>New Password</label>
                            <input type="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                            @error('password')<span class="invalid-feedback">{{ $message }}</span>@enderror
                        </div>

                        <div class="form-group">
                            <label>Confirm New Password</label>
                            <input type="password" name="password_confirmation" class="form-control" required>
                        </div>

                        <button type="submit" class="btn btn-primary">Reset Password</button>
                        <a href="{{ route('password.phone.form') }}" class="btn btn-link">Resend OTP</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
