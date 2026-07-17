@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <strong>Forgot Password</strong>
                </div>
                <div class="card-body">
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <p class="text-muted">Enter your registered phone number. OTP will be sent to this number.</p>

                    <form method="POST" action="{{ route('password.otp.send') }}">
                        @csrf
                        <div class="form-group">
                            <label for="phone">Registered Phone Number</label>
                            <input type="text" name="phone" id="phone" class="form-control" value="{{ old('phone') }}" required placeholder="Enter registered phone number">
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Send OTP</button>
                    </form>

                    <div class="text-center mt-3">
                        <a href="{{ route('login') }}">Back to Login</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
