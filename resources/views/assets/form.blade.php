@csrf

@php
    $user = auth()->user();
    $canManageStore = \App\Support\StoreAccessScope::canManageStore($user);
    $isSuperuser = \App\Support\StoreAccessScope::isSuperuser($user);
    $scopeCollegeId = \App\Support\StoreAccessScope::collegeId($user);
    $scopeDepartmentId = \App\Support\StoreAccessScope::departmentId($user);
    $scopeCollegeName = \App\Support\StoreAccessScope::collegeName($user);
    $scopeDepartmentName = \App\Support\StoreAccessScope::departmentName($user);
    $currentCollegeId = old('college_id', $asset->college_id ?: $scopeCollegeId);
    $currentDepartmentId = old('department_id', $asset->department_id ?: $scopeDepartmentId);
@endphp

<style>
    .required-label:after { content: ' *'; color:#dc3545; font-weight:bold; }
</style>

@if(!$canManageStore)
    <div class="alert alert-danger">Only Superuser or Storekeeper can add/edit assets.</div>
@endif

@if(!$isSuperuser && (!$scopeCollegeId || !$scopeDepartmentId))
    <div class="alert alert-warning">
        Your login does not have College / Directorate and Department / Office / KVK assigned.
        Please update <strong>users.college_id</strong> and <strong>users.department_id</strong> for this login.
    </div>
@endif

<div class="row">
    <div class="col-md-4 form-group">
        <label class="required-label">College / Directorate</label>
        @if($isSuperuser)
            <select id="asset_college_id" name="college_id" class="form-control" required>
                <option value="">Select</option>
                @foreach($colleges as $c)
                    <option value="{{ $c->id }}" {{ (string)$currentCollegeId === (string)$c->id ? 'selected' : '' }}>{{ $c->name }}</option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="college_id" value="{{ $scopeCollegeId }}">
            <input type="text" class="form-control" value="{{ $scopeCollegeName }}" readonly required>
        @endif
    </div>

    <div class="col-md-4 form-group">
        <label class="required-label">Department / Office / KVK</label>
        @if($isSuperuser)
            <select id="asset_department_id" name="department_id" class="form-control" required>
                <option value="">Select</option>
                @foreach($departments as $d)
                    <option value="{{ $d->id }}" data-college="{{ $d->college_id }}" {{ (string)$currentDepartmentId === (string)$d->id ? 'selected' : '' }}>
                        {{ $d->name }}{{ $d->place ? ' - '.$d->place : '' }}
                    </option>
                @endforeach
            </select>
        @else
            <input type="hidden" name="department_id" value="{{ $scopeDepartmentId }}">
            <input type="text" class="form-control" value="{{ $scopeDepartmentName }}" readonly required>
        @endif
    </div>

    <div class="col-md-4 form-group">
        <label>Note</label>
        <input class="form-control" readonly value="If assigned to employee, department will be taken from employee current posting.">
    </div>
</div>

<div class="row">
    <div class="col-md-3 form-group"><label>Asset Code</label><input name="asset_code" class="form-control" value="{{ old('asset_code',$asset->asset_code) }}"></div>
    <div class="col-md-3 form-group"><label>Inventory No</label><input name="inventory_no" class="form-control" value="{{ old('inventory_no',$asset->inventory_no) }}"></div>
    <div class="col-md-3 form-group"><label class="required-label">Category</label><select name="asset_category" class="form-control" required>@foreach($categories as $c)<option value="{{ $c }}" {{ old('asset_category',$asset->asset_category)==$c?'selected':'' }}>{{ $c }}</option>@endforeach</select></div>
    <div class="col-md-3 form-group"><label class="required-label">Item Name</label><input name="item_name" class="form-control" required value="{{ old('item_name',$asset->item_name) }}"></div>
</div>
<div class="row">
    <div class="col-md-3 form-group"><label>Make</label><input name="make" class="form-control" value="{{ old('make',$asset->make) }}"></div>
    <div class="col-md-3 form-group"><label>Model</label><input name="model" class="form-control" value="{{ old('model',$asset->model) }}"></div>
    <div class="col-md-3 form-group"><label>Serial No</label><input name="serial_no" class="form-control" value="{{ old('serial_no',$asset->serial_no) }}"></div>
    <div class="col-md-3 form-group"><label>Location / Room</label><input name="location" class="form-control" value="{{ old('location',$asset->location) }}"></div>
</div>
<div class="row">
    <div class="col-md-6 form-group"><label>Configuration / Description</label><input name="configuration" class="form-control" value="{{ old('configuration',$asset->configuration) }}" placeholder="i5, 8GB RAM, 512GB SSD etc."></div>
    <div class="col-md-3 form-group"><label>Purchase Date</label><input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', optional($asset->purchase_date)->format('Y-m-d')) }}"></div>
    <div class="col-md-3 form-group"><label>Purchase Amount</label><input type="number" step="0.01" name="purchase_amount" class="form-control" value="{{ old('purchase_amount',$asset->purchase_amount) }}"></div>
</div>
<div class="row">
    <div class="col-md-3 form-group"><label>Purchase Order No.</label><input name="purchase_order_no" class="form-control" value="{{ old('purchase_order_no',$asset->purchase_order_no) }}"></div>
    <div class="col-md-3 form-group"><label>Warranty Till</label><input type="date" name="warranty_till" class="form-control" value="{{ old('warranty_till', optional($asset->warranty_till)->format('Y-m-d')) }}"></div>
    <div class="col-md-3 form-group"><label class="required-label">Condition</label><select name="condition_status" class="form-control" required>@foreach($conditions as $c)<option value="{{ $c }}" {{ old('condition_status',$asset->condition_status)==$c?'selected':'' }}>{{ $c }}</option>@endforeach</select></div>
    <div class="col-md-3 form-group"><label class="required-label">Asset State</label><select name="asset_state" id="asset_state" class="form-control" required>@foreach($states as $s)<option value="{{ $s }}" {{ old('asset_state',$asset->asset_state)==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
</div>
<div class="row">
    <div class="col-md-4 form-group" id="assignedDiv"><label>Assigned To Employee</label><select name="assigned_to_employee_id" class="form-control"><option value="">-- Select --</option>@foreach($employees as $e)<option value="{{ $e->id }}" {{ old('assigned_to_employee_id',$asset->assigned_to_employee_id)==$e->id?'selected':'' }}>{{ $e->display_name }} - {{ $e->phone }}</option>@endforeach</select></div>
    <div class="col-md-3 form-group"><label>State Date</label><input type="date" name="state_date" class="form-control" value="{{ old('state_date', optional($asset->state_date)->format('Y-m-d')) }}"></div>
    <div class="col-md-5 form-group"><label>Remarks</label><input name="remarks" class="form-control" value="{{ old('remarks',$asset->remarks) }}"></div>
</div>
<button class="btn btn-success" {{ !$canManageStore ? 'disabled' : '' }}>Save Asset</button>
<a href="{{ route('assets.index') }}" class="btn btn-secondary">Back</a>
@push('scripts')
<script>
function filterAssetDepartments(){
    var collegeId = $('#asset_college_id').val();
    $('#asset_department_id option').each(function(){
        var optCollege = $(this).data('college');
        if(!$(this).val() || !collegeId || optCollege == collegeId){ $(this).show(); } else { $(this).hide(); }
    });
    var selected = $('#asset_department_id option:selected');
    if(collegeId && selected.val() && selected.data('college') != collegeId){ $('#asset_department_id').val(''); }
}
$('#asset_college_id').on('change', filterAssetDepartments); filterAssetDepartments();
function toggleAssigned(){ $('#assignedDiv').toggle($('#asset_state').val() === 'With Employee'); }
$('#asset_state').on('change', toggleAssigned); toggleAssigned();
</script>
@endpush
