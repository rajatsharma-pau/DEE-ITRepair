@extends('layouts.app')
@section('content')
<h3>Dashboard</h3><div class="row"><div class="col-md-3"><div class="card bg-light"><div class="card-body"><h4>{{ $employees }}</h4><p>Employees</p></div></div></div><div class="col-md-3"><div class="card bg-light"><div class="card-body"><h4>{{ $requests }}</h4><p>Total Requests</p></div></div></div><div class="col-md-3"><div class="card bg-light"><div class="card-body"><h4>{{ $submitted }}</h4><p>With Storekeeper</p></div></div></div><div class="col-md-3"><div class="card bg-light"><div class="card-body"><h4>{{ $programmer }}</h4><p>With Programmer</p></div></div></div></div>
@endsection
