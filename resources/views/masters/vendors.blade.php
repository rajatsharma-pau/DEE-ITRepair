@extends('layouts.app')
@section('content')
<h4>Vendor Master</h4>
<div class="card mb-3"><div class="card-header">Add Vendor</div><div class="card-body">
<form method="POST" action="{{ route('masters.vendors.store') }}">
@csrf
<div class="row">
    <div class="col-md-3 form-group"><label class="required">Vendor Name</label><input name="name" class="form-control" required></div>
    <div class="col-md-2 form-group"><label>Type</label><select name="vendor_type" class="form-control"><option>Computer</option><option>Electrical</option><option>Furniture</option><option>General</option><option>Other</option></select></div>
    <div class="col-md-2 form-group"><label>Contact Person</label><input name="contact_person" class="form-control"></div>
    <div class="col-md-2 form-group"><label>Mobile</label><input name="mobile" class="form-control"></div>
    <div class="col-md-3 form-group"><label>Email</label><input name="email" class="form-control"></div>
    <div class="col-md-4 form-group"><label>GST No.</label><input name="gst_no" class="form-control"></div>
    <div class="col-md-4 form-group"><label>PAN No.</label><input name="pan_no" class="form-control"></div>
    <div class="col-md-4 form-group"><label>Active</label><br><input type="checkbox" name="is_active" checked> Active</div>
    <div class="col-md-12 form-group"><label>Address</label><textarea name="address" class="form-control" rows="2"></textarea></div>
</div>
<button class="btn btn-success">Save Vendor</button>
</form>
</div></div>
<div class="card"><div class="card-header">Vendors</div><div class="card-body table-responsive">
<table class="table table-bordered table-sm">
<thead><tr><th>Name</th><th>Type</th><th>Contact</th><th>Mobile</th><th>GST</th><th>PAN</th><th>Status</th></tr></thead>
<tbody>
@foreach($items as $item)
<tr>
    <td>{{ $item->name }}</td>
    <td>{{ $item->vendor_type }}</td>
    <td>{{ $item->contact_person }}</td>
    <td>{{ $item->mobile }}</td>
    <td>{{ $item->gst_no }}</td>
    <td>{{ $item->pan_no }}</td>
    <td>{{ $item->is_active ? 'Active' : 'Inactive' }}</td>
</tr>
@endforeach
</tbody>
</table>
</div></div>
@endsection
