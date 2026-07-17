@extends('layouts.app')

@section('content')
@php
    $loginUser = Auth::user();

    $hasAny = function ($roles) use ($loginUser) {
        if (!$loginUser) {
            return false;
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (method_exists($loginUser, 'hasAnyRole')) {
            return $loginUser->hasAnyRole($roles);
        }

        foreach ($roles as $role) {
            if (method_exists($loginUser, 'hasRole') && $loginUser->hasRole($role)) {
                return true;
            }
            if (method_exists($loginUser, 'isRole') && $loginUser->isRole($role)) {
                return true;
            }
            if (isset($loginUser->role) && $loginUser->role == $role) {
                return true;
            }
        }

        return false;
    };

    $canEditEmployee = $hasAny(['superuser','admin','college_admin','department_admin','director']);
    if (method_exists(\App\Support\AccessScope::class, 'canEditEmployee')) {
        try {
            $canEditEmployee = \App\Support\AccessScope::canEditEmployee($employee, $loginUser);
        } catch (\Throwable $e) {
            try {
                $canEditEmployee = \App\Support\AccessScope::canEditEmployee($employee);
            } catch (\Throwable $e2) {
                $canEditEmployee = $hasAny(['superuser','admin','college_admin','department_admin','director']);
            }
        }
    }

    $canTransferEmployee = $canEditEmployee;
    if (method_exists(\App\Support\AccessScope::class, 'canTransferEmployee')) {
        try {
            $canTransferEmployee = \App\Support\AccessScope::canTransferEmployee($employee, $loginUser);
        } catch (\Throwable $e) {
            try {
                $canTransferEmployee = \App\Support\AccessScope::canTransferEmployee($employee);
            } catch (\Throwable $e2) {
                $canTransferEmployee = $canEditEmployee;
            }
        }
    }

    $canManageService = $canEditEmployee;
    $canManageCharges = $canEditEmployee;
    $canViewAssetRegister = $hasAny(['superuser','admin','college_admin','department_admin','director','storekeeper']);

    $formatDate = function ($date) {
        if (!$date) {
            return '—';
        }
        try {
            if ($date instanceof \Carbon\Carbon) {
                return $date->format('d-m-Y');
            }
            return \Carbon\Carbon::parse($date)->format('d-m-Y');
        } catch (\Exception $e) {
            return $date;
        }
    };

    $showValue = function ($value) {
        return $value !== null && $value !== '' ? $value : '—';
    };

    $roleNames = [];
    if ($employee->user && method_exists($employee->user, 'roleNames')) {
        $roleNames = $employee->user->roleNames();
    } elseif ($employee->user && isset($employee->user->role)) {
        $roleNames = [$employee->user->role];
    }
    if ($roleNames instanceof \Illuminate\Support\Collection) {
        $roleNames = $roleNames->toArray();
    }
    if (!is_array($roleNames)) {
        $roleNames = [$roleNames];
    }
    $roleNames = array_filter($roleNames);

    $roleLabel = function ($role) {
        return ucwords(str_replace('_', ' ', $role));
    };

    $currentCollege = optional($employee->college)->name;
    $currentDepartment = optional($employee->department)->name;
    if (optional($employee->department)->place) {
        $currentDepartment .= ' - '.optional($employee->department)->place;
    }
@endphp

@push('styles')
<style>
    .employee-page .required:after { content:' *'; color:#dc3545; font-weight:700; }
    .employee-page .profile-photo { width:128px; height:128px; object-fit:cover; border-radius:50%; border:4px solid #fff; box-shadow:0 3px 14px rgba(0,0,0,.18); }
    .employee-page .profile-placeholder { width:128px; height:128px; border-radius:50%; display:inline-flex; align-items:center; justify-content:center; font-size:42px; font-weight:800; color:#1f7f4c; background:#e8f6f0; border:4px solid #fff; box-shadow:0 3px 14px rgba(0,0,0,.18); }
    .employee-page .mini-label { display:block; font-size:11px; color:#6c757d; text-transform:uppercase; letter-spacing:.04em; margin-bottom:2px; }
    .employee-page .info-card { border-left:4px solid #1f7f4c; }
    .employee-page .info-value { font-weight:700; color:#212529; }
    .employee-page .section-title { font-weight:700; margin-bottom:0; }
    .employee-page .table th { background:#f8f9fa; white-space:nowrap; width:17%; }
    .employee-page .badge-role { font-size:12px; margin:2px; padding:6px 8px; }
    .employee-page .history-table td, .employee-page .history-table th { vertical-align:middle; }
    .employee-page .form-help { font-size:12px; color:#6c757d; }
    .employee-page .sticky-profile { position:sticky; top:15px; }
    @media(max-width: 991.98px) { .employee-page .sticky-profile { position:static; } }
</style>
@endpush

<div class="employee-page">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h4 class="mb-1">{{ $employee->display_name }}</h4>
            <div class="text-muted">
                Employee Code: <strong>{{ $showValue($employee->employee_code) }}</strong>
                <span class="mx-1">|</span>
                Status: <span class="badge badge-{{ $employee->status == 'Active' ? 'success' : 'secondary' }}">{{ $showValue($employee->status) }}</span>
            </div>
        </div>
        <div class="mt-2 mt-md-0">
            @if($canEditEmployee)
                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-primary">Edit Employee</a>
            @endif
            <a href="{{ route('employees.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>

    <div class="alert alert-info py-2">
        <strong>Note:</strong> Fields marked with <span class="text-danger font-weight-bold">*</span> are mandatory in action forms. Keep order/document details wherever available for proper service record tracking.
    </div>

    <div class="row">
        <div class="col-lg-4">
            <div class="card mb-3 sticky-profile">
                <div class="card-body text-center">
                    @if($employee->photo_url)
                        <img src="{{ $employee->photo_url }}" class="profile-photo" alt="Employee Photo" loading="lazy">
                    @else
                        <span class="profile-placeholder">{{ strtoupper(substr($employee->display_name ?: 'E', 0, 1)) }}</span>
                    @endif

                    <h5 class="mt-3 mb-1">{{ $employee->display_name }}</h5>
                    <div class="text-muted mb-2">{{ $employee->designation_name ?: 'Designation not set' }}</div>

                    <div class="mb-2">
                        @forelse($roleNames as $roleName)
                            <span class="badge badge-primary badge-role">{{ $roleLabel($roleName) }}</span>
                        @empty
                            <span class="badge badge-light badge-role">Employee</span>
                        @endforelse
                    </div>

                    <hr>
                    <div class="text-left">
                        <span class="mini-label">College / Directorate</span>
                        <div class="info-value mb-2">{{ $showValue($currentCollege) }}</div>
                        <span class="mini-label">Department / Office / KVK</span>
                        <div class="info-value mb-2">{{ $showValue($currentDepartment) }}</div>
                        <span class="mini-label">Internal Section</span>
                        <div class="info-value mb-2">{{ $showValue(optional($employee->section)->name) }}</div>
                        <span class="mini-label">Room No.</span>
                        <div class="info-value">{{ $showValue($employee->room_no) }}</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="card info-card h-100"><div class="card-body py-3">
                        <span class="mini-label">Phone / Login ID</span>
                        <div class="info-value">{{ $showValue($employee->phone) }}</div>
                    </div></div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card info-card h-100"><div class="card-body py-3">
                        <span class="mini-label">Date of Joining</span>
                        <div class="info-value">{{ $formatDate($employee->date_of_joining) }}</div>
                    </div></div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="card info-card h-100"><div class="card-body py-3">
                        <span class="mini-label">Retirement Date</span>
                        <div class="info-value">{{ $formatDate($employee->final_retirement_date) }}</div>
                    </div></div>
                </div>
            </div>

            <div class="card mb-3">
                <div class="card-header"><h6 class="section-title">Employee Details</h6></div>
                <div class="card-body table-responsive">
                    <table class="table table-bordered table-sm mb-0">
                        <tr><th>Phone/Login ID</th><td>{{ $showValue($employee->phone) }}</td><th>Email</th><td>{{ $showValue($employee->email) }}</td></tr>
                        <tr><th>College / Directorate</th><td>{{ $showValue($currentCollege) }}</td><th>Department / Office / KVK</th><td>{{ $showValue($currentDepartment) }}</td></tr>
                        <tr><th>Internal Section</th><td>{{ $showValue(optional($employee->section)->name) }}</td><th>Room No.</th><td>{{ $showValue($employee->room_no) }}</td></tr>
                        <tr><th>Designation</th><td>{{ $showValue($employee->designation_name) }}</td><th>Job Type</th><td>{{ $showValue($employee->job_type) }}</td></tr>
                        <tr><th>GPF No.</th><td>{{ $showValue($employee->gpf_no) }}</td><th>NPS No.</th><td>{{ $showValue($employee->nps_no) }}</td></tr>
                        <tr><th>PAN No.</th><td>{{ $showValue($employee->pan_no) }}</td><th>Aadhaar No.</th><td>{{ $showValue($employee->aadhaar_no) }}</td></tr>
                        <tr><th>DOB</th><td>{{ $formatDate($employee->date_of_birth) }}</td><th>DOJ</th><td>{{ $formatDate($employee->date_of_joining) }}</td></tr>
                        <tr><th>Retirement</th><td>{{ $formatDate($employee->final_retirement_date) }}</td><th>Annual Increment</th><td>{{ $formatDate($employee->final_increment_date) }}</td></tr>
                        <tr><th>Address</th><td colspan="3">{{ trim($employee->address_line_1.' '.$employee->address_line_2.', '.$employee->manual_city.', '.$employee->manual_state.', '.$employee->manual_country.' - '.$employee->zip, ' ,.-') ?: '—' }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header d-flex flex-wrap justify-content-between align-items-center">
            <h6 class="section-title">Allocated Assets</h6>
            @if($canViewAssetRegister)
                <a href="{{ route('assets.index', ['employee_id' => $employee->id]) }}" class="btn btn-sm btn-primary mt-2 mt-md-0">View in Asset Register</a>
            @endif
        </div>
        <div class="card-body table-responsive">
            <table class="table table-bordered table-sm history-table mb-0">
                <thead><tr><th>Asset</th><th>Inventory No.</th><th>Category</th><th>Make/Model</th><th>State</th><th>Location/Room</th><th>Action</th></tr></thead>
                <tbody>
                @forelse($employee->assets as $asset)
                    <tr>
                        <td>{{ $showValue($asset->item_name) }}</td>
                        <td>{{ $showValue($asset->inventory_no) }}</td>
                        <td>{{ $showValue($asset->asset_category) }}</td>
                        <td>{{ trim($asset->make.' '.$asset->model) ?: '—' }}</td>
                        <td><span class="badge badge-info">{{ $showValue($asset->asset_state) }}</span></td>
                        <td>{{ $showValue($asset->location ?: $employee->room_no) }}</td>
                        <td><a href="{{ route('assets.show', $asset) }}" class="btn btn-sm btn-outline-primary">View</a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted text-center">No asset currently allocated.</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h6 class="section-title">Department Transfer / Posting History</h6></div>
        <div class="card-body">
            @if($canTransferEmployee)
            <form method="POST" action="{{ route('employees.transfers.store', $employee) }}" enctype="multipart/form-data" class="border rounded p-3 mb-3 bg-light" id="employee-transfer-form">
                @csrf
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>New Transfer / Posting</strong>
                    <small class="text-muted">Destination can be selected as per your transfer permission.</small>
                </div>
                <div class="row">
                    <div class="col-md-4 form-group"><label class="required">To College / Directorate</label><select id="transfer_college_id" name="to_college_id" class="form-control" required><option value="">Select</option>@foreach($colleges as $c)<option value="{{ $c->id }}">{{ $c->name }}</option>@endforeach</select></div>
                    <div class="col-md-4 form-group"><label class="required">To Department / Office / KVK</label><select id="transfer_department_id" name="to_department_id" class="form-control" required><option value="">Select</option>@foreach($departments as $d)<option value="{{ $d->id }}" data-college="{{ $d->college_id }}">{{ $d->name }}{{ $d->place ? ' - '.$d->place : '' }}</option>@endforeach</select></div>
                    <div class="col-md-2 form-group"><label class="required">Transfer Date</label><input type="date" name="transfer_date" class="form-control" value="{{ old('transfer_date', date('Y-m-d')) }}" required></div>
                    <div class="col-md-2 form-group"><label>Joining Date</label><input type="date" name="joining_date" class="form-control"></div>
                    <div class="col-md-2 form-group"><label>Relieving Date</label><input type="date" name="relieving_date" class="form-control"></div>
                    <div class="col-md-2 form-group"><label>Order No.</label><input name="order_no" class="form-control" placeholder="Order no."></div>
                    <div class="col-md-2 form-group"><label>Order Date</label><input type="date" name="order_date" class="form-control"></div>
                    <div class="col-md-3 form-group"><label>Order PDF/Image</label><input type="file" name="order_file" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png"><small class="form-help">Upload order copy if available.</small></div>
                    <div class="col-md-3 form-group"><label>Remarks</label><input name="remarks" class="form-control" placeholder="Remarks"></div>
                </div>
                <button class="btn btn-sm btn-success">Transfer Employee</button>
            </form>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-sm history-table mb-0">
                    <thead><tr><th>Transfer Date</th><th>From</th><th>To</th><th>Relieving</th><th>Joining</th><th>Order</th><th>Remarks</th></tr></thead>
                    <tbody>
                    @forelse($employee->transfers as $t)
                        <tr>
                            <td>{{ $formatDate($t->transfer_date) }}</td>
                            <td>{{ $showValue(optional($t->fromCollege)->name) }}<br><small>{{ $showValue(optional($t->fromDepartment)->name) }}</small></td>
                            <td>{{ $showValue(optional($t->toCollege)->name) }}<br><small>{{ $showValue(optional($t->toDepartment)->name) }}</small></td>
                            <td>{{ $formatDate($t->relieving_date) }}</td>
                            <td>{{ $formatDate($t->joining_date) }}</td>
                            <td>{{ $showValue($t->order_no) }}</td>
                            <td>{{ $showValue($t->remarks) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted text-center">No transfer/posting history available.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h6 class="section-title">Service Movement / Promotion History</h6></div>
        <div class="card-body">
            @if($canManageService)
            <form method="POST" action="{{ route('employees.service-movements.store', $employee) }}" enctype="multipart/form-data" class="border rounded p-3 mb-3 bg-light" id="employee-movement-form">
                @csrf
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <strong>New Service Movement</strong>
                    <small class="text-muted">Use this for joining, promotion, transfer entry, additional charge, retirement, etc.</small>
                </div>
                <div class="row">
                    <div class="col-md-2 form-group"><label class="required">Type</label><select name="movement_type" id="movement_type" class="form-control" required>@foreach(['Joining','Promotion','Transfer','Additional Charge','Reversion','Retirement','Resignation','Contract Extension','Other'] as $m)<option>{{ $m }}</option>@endforeach</select></div>
                    <div class="col-md-2 form-group"><label>From Designation</label><select name="from_designation_id" class="form-control"><option value="">Select</option>@foreach($designations as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></div>
                    <div class="col-md-2 form-group"><label>To Designation</label><select name="to_designation_id" id="to_designation_id" class="form-control"><option value="">Select</option>@foreach($designations as $d)<option value="{{ $d->id }}">{{ $d->name }}</option>@endforeach</select></div>
                    <div class="col-md-2 form-group"><label>Manual To Designation</label><input name="manual_to_designation" id="manual_to_designation" class="form-control" placeholder="If not in master"></div>
                    <div class="col-md-2 form-group"><label class="required">Effective Date</label><input type="date" name="effective_date" class="form-control" value="{{ old('effective_date', date('Y-m-d')) }}" required></div>
                    <div class="col-md-2 form-group"><label>Order No.</label><input name="order_no" class="form-control"></div>
                    <div class="col-md-2 form-group"><label>Order Date</label><input type="date" name="order_date" class="form-control"></div>
                    <div class="col-md-3 form-group"><label>Order PDF/Image</label><input type="file" name="document" class="form-control-file" accept=".pdf,.jpg,.jpeg,.png"></div>
                    <div class="col-md-7 form-group"><label>Remarks</label><input name="remarks" class="form-control" placeholder="Brief remarks"></div>
                </div>
                <div class="form-help mb-2">For Promotion/Reversion, select To Designation or enter Manual To Designation.</div>
                <button class="btn btn-sm btn-success">Add Movement</button>
            </form>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-sm history-table mb-0">
                    <thead><tr><th>Date</th><th>Type</th><th>From</th><th>To</th><th>Order</th><th>Remarks</th></tr></thead>
                    <tbody>
                    @forelse($employee->serviceMovements as $m)
                        <tr>
                            <td>{{ $formatDate($m->effective_date) }}</td>
                            <td><span class="badge badge-secondary">{{ $showValue($m->movement_type) }}</span></td>
                            <td>{{ $showValue(optional($m->fromDesignation)->name ?: $m->manual_from_designation) }}</td>
                            <td>{{ $showValue(optional($m->toDesignation)->name ?: $m->manual_to_designation) }}</td>
                            <td>{{ $showValue($m->order_no) }}</td>
                            <td>{{ $showValue($m->remarks) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted text-center">No service movement/promotion history available.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header"><h6 class="section-title">Additional Charges</h6></div>
        <div class="card-body">
            @if($canManageCharges)
            <form method="POST" action="{{ route('employees.charges.store', $employee) }}" class="border rounded p-3 mb-3 bg-light">
                @csrf
                <div class="row">
                    <div class="col-md-3 form-group"><label class="required">Charge Name</label><input name="charge_name" class="form-control" placeholder="Store Incharge / Head / Office Incharge" required></div>
                    <div class="col-md-2 form-group"><label class="required">From</label><input type="date" name="from_date" class="form-control" value="{{ old('from_date', date('Y-m-d')) }}" required></div>
                    <div class="col-md-2 form-group"><label>To</label><input type="date" name="to_date" class="form-control"></div>
                    <div class="col-md-3 form-group"><label>Remarks</label><input name="remarks" class="form-control"></div>
                    <div class="col-md-2 form-group pt-4"><label><input type="checkbox" name="is_active" value="1" checked> Active</label></div>
                </div>
                <button class="btn btn-sm btn-success">Add Charge</button>
            </form>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-sm history-table mb-0">
                    <thead><tr><th>Charge</th><th>From</th><th>To</th><th>Active</th><th>Remarks</th></tr></thead>
                    <tbody>
                    @forelse($employee->charges as $c)
                        <tr>
                            <td>{{ $showValue($c->charge_name) }}</td>
                            <td>{{ $formatDate($c->from_date) }}</td>
                            <td>{{ $formatDate($c->to_date) }}</td>
                            <td><span class="badge badge-{{ $c->is_active ? 'success' : 'secondary' }}">{{ $c->is_active ? 'Yes' : 'No' }}</span></td>
                            <td>{{ $showValue($c->remarks) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted text-center">No additional charge assigned.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function filterTransferDepartments(){
    var collegeId = $('#transfer_college_id').val();
    $('#transfer_department_id option').each(function(){
        var optCollege = $(this).data('college');
        if(!$(this).val() || !collegeId || optCollege == collegeId){
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    var selected = $('#transfer_department_id option:selected');
    if(collegeId && selected.val() && selected.data('college') != collegeId){
        $('#transfer_department_id').val('');
    }
}
$('#transfer_college_id').on('change', filterTransferDepartments);
filterTransferDepartments();

$('#employee-movement-form').on('submit', function(e){
    var movementType = $('#movement_type').val();
    var toDesignation = $('#to_designation_id').val();
    var manualToDesignation = $.trim($('#manual_to_designation').val());

    if((movementType === 'Promotion' || movementType === 'Reversion') && !toDesignation && manualToDesignation === ''){
        e.preventDefault();
        alert('For ' + movementType + ', please select To Designation or enter Manual To Designation.');
        $('#to_designation_id').focus();
        return false;
    }
});
</script>
@endpush

@endsection
