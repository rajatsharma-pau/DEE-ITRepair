@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between mb-3">
    <h4>{{ $employee->display_name }}</h4>
    <div><a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary">Edit</a> <a href="{{ route('employees.index') }}" class="btn btn-secondary">Back</a></div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="card mb-3"><div class="card-header">Profile</div><div class="card-body text-center">
            @if($employee->photo_url)<img src="{{ $employee->photo_url }}" style="width:150px;height:150px;object-fit:cover;border-radius:50%;">@endif
            <h5 class="mt-2">{{ $employee->display_name }}</h5>
            <p>{{ $employee->designation_name }}<br>{{ optional($employee->college)->name }}<br>{{ optional($employee->department)->name }}<br>{{ optional($employee->section)->name }}</p>
        </div></div>
    </div>
    <div class="col-md-8">
        <div class="card mb-3"><div class="card-header">Employee Details</div><div class="card-body table-responsive">
            <table class="table table-bordered table-sm">
                <tr><th>Phone</th><td>{{ $employee->phone }}</td><th>Email</th><td>{{ $employee->email }}</td></tr>
                <tr><th>College / Directorate</th><td>{{ optional($employee->college)->name }}</td><th>Department / Office / KVK</th><td>{{ optional($employee->department)->name }}{{ optional($employee->department)->place ? ' - '.optional($employee->department)->place : '' }}</td></tr>
                <tr><th>Internal Section</th><td>{{ optional($employee->section)->name }}</td><th>Room No</th><td>{{ $employee->room_no }}</td></tr>
                <tr><th>GPF</th><td>{{ $employee->gpf_no }}</td><th>NPS</th><td>{{ $employee->nps_no }}</td></tr>
                <tr><th>PAN</th><td>{{ $employee->pan_no }}</td><th>Aadhaar</th><td>{{ $employee->aadhaar_no }}</td></tr>
                <tr><th>DOB</th><td>{{ optional($employee->date_of_birth)->format('d-m-Y') }}</td><th>DOJ</th><td>{{ optional($employee->date_of_joining)->format('d-m-Y') }}</td></tr>
                <tr><th>Retirement</th><td>{{ optional($employee->final_retirement_date)->format('d-m-Y') }}</td><th>Annual Increment</th><td>{{ optional($employee->final_increment_date)->format('d-m-Y') }}</td></tr>
                <tr><th>Address</th><td colspan="3">{{ $employee->address_line_1 }} {{ $employee->address_line_2 }}, {{ $employee->manual_city }}, {{ $employee->manual_state }}, {{ $employee->manual_country }} - {{ $employee->zip }}</td></tr>
            </table>
        </div></div>
    </div>
</div>


<div class="card mb-3"><div class="card-header d-flex justify-content-between">
    <span>Allocated Assets</span>
    @if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))
        <a href="{{ route('assets.index', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-primary">View All in Asset Register</a>
    @endif
</div><div class="card-body table-responsive">
    <table class="table table-bordered table-sm">
        <thead><tr><th>Asset</th><th>Inventory No.</th><th>Category</th><th>Make/Model</th><th>State</th><th>Location/Room</th><th>Action</th></tr></thead>
        <tbody>
        @forelse($employee->assets as $asset)
            <tr>
                <td>{{ $asset->item_name }}</td>
                <td>{{ $asset->inventory_no }}</td>
                <td>{{ $asset->asset_category }}</td>
                <td>{{ $asset->make }} {{ $asset->model }}</td>
                <td><span class="badge badge-info">{{ $asset->asset_state }}</span></td>
                <td>{{ $asset->location ?: $employee->room_no }}</td>
                <td><a href="{{ route('assets.show', $asset) }}" class="btn btn-sm btn-primary">View</a></td>
            </tr>
        @empty
            <tr><td colspan="7">No asset currently allocated.</td></tr>
        @endforelse
        </tbody>
    </table>
</div></div>

<div class="card mb-3"><div class="card-header">Department Transfer / Posting History</div><div class="card-body">
    @if(Auth::user()->isRole(['admin','college_admin','department_admin','director']))
    <form method="POST" action="{{ route('employees.transfers.store', $employee) }}" enctype="multipart/form-data" class="border p-2 mb-3">
        @csrf
        <div class="row">
            <div class="col-md-4 form-group"><label>To College / Directorate</label><select id="transfer_college_id" name="to_college_id" class="form-control" required><option value="">Select</option>@foreach($colleges as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
            <div class="col-md-4 form-group"><label>To Department / Office / KVK</label><select id="transfer_department_id" name="to_department_id" class="form-control" required><option value="">Select</option>@foreach($departments as $d)<option value="{{ $d->id }}" data-college="{{ $d->college_id }}">{{ $d->name }}{{ $d->place ? ' - '.$d->place : '' }}</option>@endforeach</select></div>
            <div class="col-md-2 form-group"><label>Transfer Date</label><input type="date" name="transfer_date" class="form-control" required></div>
            <div class="col-md-2 form-group"><label>Joining Date</label><input type="date" name="joining_date" class="form-control"></div>
            <div class="col-md-2 form-group"><label>Relieving Date</label><input type="date" name="relieving_date" class="form-control"></div>
            <div class="col-md-2 form-group"><label>Order No.</label><input name="order_no" class="form-control"></div>
            <div class="col-md-2 form-group"><label>Order Date</label><input type="date" name="order_date" class="form-control"></div>
            <div class="col-md-3 form-group"><label>Order PDF/Image</label><input type="file" name="order_file" class="form-control-file"></div>
            <div class="col-md-3 form-group"><label>Remarks</label><input name="remarks" class="form-control"></div>
        </div>
        <button class="btn btn-sm btn-success">Transfer Employee</button>
    </form>
    @endif
    <table class="table table-bordered table-sm">
        <thead><tr><th>Transfer Date</th><th>From</th><th>To</th><th>Relieving</th><th>Joining</th><th>Order</th><th>Remarks</th></tr></thead>
        <tbody>
        @foreach($employee->transfers as $t)
            <tr>
                <td>{{ optional($t->transfer_date)->format('d-m-Y') }}</td>
                <td>{{ optional($t->fromCollege)->name }}<br><small>{{ optional($t->fromDepartment)->name }}</small></td>
                <td>{{ optional($t->toCollege)->name }}<br><small>{{ optional($t->toDepartment)->name }}</small></td>
                <td>{{ optional($t->relieving_date)->format('d-m-Y') }}</td>
                <td>{{ optional($t->joining_date)->format('d-m-Y') }}</td>
                <td>{{ $t->order_no }}</td>
                <td>{{ $t->remarks }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
</div></div>

<div class="card mb-3"><div class="card-header">Service Movement / Promotion History</div><div class="card-body">
    @if(Auth::user()->isRole(['admin','college_admin','department_admin','director']))
    <form method="POST" action="{{ route('employees.service-movements.store', $employee) }}" enctype="multipart/form-data" class="border p-2 mb-3">
        @csrf
        <div class="row">
            <div class="col-md-2 form-group"><label>Type</label><select name="movement_type" class="form-control">@foreach(['Joining','Promotion','Transfer','Additional Charge','Reversion','Retirement','Resignation','Contract Extension','Other'] as $m)<option>{{ $m }}</option>@endforeach</select></div>
            <div class="col-md-2 form-group"><label>From Designation</label><select name="from_designation_id" class="form-control"><option value="">Select</option>@foreach($designations as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></div>
            <div class="col-md-2 form-group"><label>To Designation</label><select name="to_designation_id" class="form-control"><option value="">Select</option>@foreach($designations as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></div>
            <div class="col-md-2 form-group"><label>Manual To Designation</label><input name="manual_to_designation" class="form-control"></div>
            <div class="col-md-2 form-group"><label>Effective Date</label><input type="date" name="effective_date" class="form-control" required></div>
            <div class="col-md-2 form-group"><label>Order No</label><input name="order_no" class="form-control"></div>
            <div class="col-md-2 form-group"><label>Order Date</label><input type="date" name="order_date" class="form-control"></div>
            <div class="col-md-3 form-group"><label>Order PDF/Image</label><input type="file" name="document" class="form-control-file"></div>
            <div class="col-md-7 form-group"><label>Remarks</label><input name="remarks" class="form-control"></div>
        </div>
        <button class="btn btn-sm btn-success">Add Movement</button>
    </form>
    @endif
    <table class="table table-bordered table-sm">
        <thead><tr><th>Date</th><th>Type</th><th>From</th><th>To</th><th>Order</th><th>Remarks</th></tr></thead>
        <tbody>@foreach($employee->serviceMovements as $m)<tr><td>{{ optional($m->effective_date)->format('d-m-Y') }}</td><td>{{ $m->movement_type }}</td><td>{{ optional($m->fromDesignation)->name ?: $m->manual_from_designation }}</td><td>{{ optional($m->toDesignation)->name ?: $m->manual_to_designation }}</td><td>{{ $m->order_no }}</td><td>{{ $m->remarks }}</td></tr>@endforeach</tbody>
    </table>
</div></div>

<div class="card mb-3"><div class="card-header">Additional Charges</div><div class="card-body">
    @if(Auth::user()->isRole(['admin','college_admin','department_admin','director']))
    <form method="POST" action="{{ route('employees.charges.store', $employee) }}" class="border p-2 mb-3">
        @csrf
        <div class="row">
            <div class="col-md-3 form-group"><label>Charge Name</label><input name="charge_name" class="form-control" placeholder="Store Incharge / Head" required></div>
            <div class="col-md-2 form-group"><label>From</label><input type="date" name="from_date" class="form-control"></div>
            <div class="col-md-2 form-group"><label>To</label><input type="date" name="to_date" class="form-control"></div>
            <div class="col-md-3 form-group"><label>Remarks</label><input name="remarks" class="form-control"></div>
            <div class="col-md-2 form-group pt-4"><label><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
        </div>
        <button class="btn btn-sm btn-success">Add Charge</button>
    </form>
    @endif
    <table class="table table-bordered table-sm"><thead><tr><th>Charge</th><th>From</th><th>To</th><th>Active</th><th>Remarks</th></tr></thead><tbody>@foreach($employee->charges as $c)<tr><td>{{ $c->charge_name }}</td><td>{{ optional($c->from_date)->format('d-m-Y') }}</td><td>{{ optional($c->to_date)->format('d-m-Y') }}</td><td>{{ $c->is_active?'Yes':'No' }}</td><td>{{ $c->remarks }}</td></tr>@endforeach</tbody></table>
</div></div>

@push('scripts')
<script>
function filterTransferDepartments(){
    var collegeId = $('#transfer_college_id').val();
    $('#transfer_department_id option').each(function(){
        var optCollege = $(this).data('college');
        if(!$(this).val() || !collegeId || optCollege == collegeId){ $(this).show(); } else { $(this).hide(); }
    });
    var selected = $('#transfer_department_id option:selected');
    if(collegeId && selected.val() && selected.data('college') != collegeId){ $('#transfer_department_id').val(''); }
}
$('#transfer_college_id').on('change', filterTransferDepartments);
filterTransferDepartments();
</script>
@endpush

@endsection
