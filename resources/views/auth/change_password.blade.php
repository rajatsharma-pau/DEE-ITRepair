@extends('layouts.dee')
@section('content')
<div class="card"><div class="card-header">Change Password</div><div class="card-body"><form method="POST">@csrf
<div class="form-group"><label>New Password</label><input type="password" name="password" class="form-control" required></div>
<div class="form-group"><label>Confirm Password</label><input type="password" name="password_confirmation" class="form-control" required></div>
<button class="btn btn-success">Change Password</button></form></div></div>
@endsection
