@csrf

@php
    $user = auth()->user();
    $storeItem = isset($item) ? $item : (isset($storeItem) ? $storeItem : null);

    $canManageStore = \App\Support\StoreAccessScope::canManageStore($user);
    $isSuperuser = \App\Support\StoreAccessScope::isSuperuser($user);

    $scopeCollegeId = \App\Support\StoreAccessScope::collegeId($user);
    $scopeDepartmentId = \App\Support\StoreAccessScope::departmentId($user);
    $scopeCollegeName = \App\Support\StoreAccessScope::collegeName($user);
    $scopeDepartmentName = \App\Support\StoreAccessScope::departmentName($user);

    $currentCollegeId = old('college_id', optional($storeItem)->college_id ?: $scopeCollegeId);
    $currentDepartmentId = old('department_id', optional($storeItem)->department_id ?: $scopeDepartmentId);
@endphp

<style>
    .required-label:after {
        content: ' *';
        color: #dc3545;
        font-weight: bold;
    }
</style>

@if(!$canManageStore)
    <div class="alert alert-danger">
        You can view store records only. Add/Edit Store Item is allowed only to Superuser or Storekeeper.
    </div>
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
            <select id="stock_college_id" name="college_id" class="form-control" required>
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
            <select id="stock_department_id" name="department_id" class="form-control" required>
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
</div>

<div class="row">
    <div class="col-md-3 form-group">
        <label class="required-label">Item Code</label>
        <input name="item_code" class="form-control" required value="{{ old('item_code', optional($storeItem)->item_code) }}" placeholder="Example: A4-PAPER">
    </div>

    <div class="col-md-4 form-group">
        <label class="required-label">Item Name</label>
        <input name="name" class="form-control" required value="{{ old('name', optional($storeItem)->name) }}" placeholder="A4 Paper, Pen, File Cover">
    </div>

    <div class="col-md-3 form-group">
        <label class="required-label">Category</label>
        <input name="category" class="form-control" required value="{{ old('category', optional($storeItem)->category) }}" placeholder="Stationery / Computer / Electrical">
    </div>

    <div class="col-md-2 form-group">
        <label class="required-label">Unit</label>
        <input name="unit" class="form-control" required value="{{ old('unit', optional($storeItem)->unit ?: 'Nos') }}" placeholder="Nos / Pkt / Ream">
    </div>
</div>

<div class="row">
    <div class="col-md-3 form-group">
        <label>Brand</label>
        <input name="brand" class="form-control" value="{{ old('brand', optional($storeItem)->brand) }}" placeholder="Optional: HP / Dell / Camlin">
        <small class="form-text text-muted">Company or make of the item. Optional.</small>
    </div>

    <div class="col-md-3 form-group">
        <label class="required-label">Opening Stock</label>
        <input type="number" min="0" step="1" name="opening_stock" class="form-control" required value="{{ old('opening_stock', optional($storeItem)->opening_stock !== null ? optional($storeItem)->opening_stock : 0) }}">
        <small class="form-text text-muted">Quantity available when this item is first created.</small>
    </div>

    <div class="col-md-3 form-group">
        <label class="required-label">Reorder Level</label>
        <input type="number" min="0" step="1" name="reorder_level" class="form-control" required value="{{ old('reorder_level', optional($storeItem)->reorder_level !== null ? optional($storeItem)->reorder_level : 0) }}">
        <small class="form-text text-muted">Minimum stock alert level. Example: if set to 10, item becomes Low Stock at 10 or below.</small>
    </div>

    <div class="col-md-3 form-group">
        <label class="required-label">Location</label>
        <input name="location" class="form-control" required value="{{ old('location', optional($storeItem)->location) }}" placeholder="Store room / Rack / Almirah">
        <small class="form-text text-muted">Physical place where the item is kept.</small>
    </div>
</div>

<div class="form-group">
    <label>Description</label>
    <textarea name="description" class="form-control" rows="3">{{ old('description', optional($storeItem)->description) }}</textarea>
</div>

<div class="form-group">
    <label>
        <input type="checkbox" name="is_active" value="1" {{ old('is_active', optional($storeItem)->is_active === null ? 1 : optional($storeItem)->is_active) ? 'checked' : '' }}>
        Active
    </label>
</div>

<button class="btn btn-success" {{ !$canManageStore ? 'disabled' : '' }}>Save</button>
<a href="{{ route('store-items.index') }}" class="btn btn-secondary">Back</a>

@push('scripts')
<script>
function filterStockDepartments(){
    var collegeId = $('#stock_college_id').val();
    $('#stock_department_id option').each(function(){
        var optCollege = $(this).data('college');
        if(!$(this).val() || !collegeId || optCollege == collegeId){
            $(this).show();
        } else {
            $(this).hide();
        }
    });
    var selected = $('#stock_department_id option:selected');
    if(collegeId && selected.val() && selected.data('college') != collegeId){
        $('#stock_department_id').val('');
    }
}
$('#stock_college_id').on('change', filterStockDepartments);
filterStockDepartments();
</script>
@endpush
