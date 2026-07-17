@extends('layouts.app')

@section('content')
@php
    $loggedUser = Auth::user();
    $canSelectEmployee = false;

    if ($loggedUser) {
        if (method_exists($loggedUser, 'hasAnyRole')) {
            $canSelectEmployee = $loggedUser->hasAnyRole(['superuser','admin','college_admin','department_admin','director','storekeeper']);
        } elseif (method_exists($loggedUser, 'isRole')) {
            $canSelectEmployee = $loggedUser->isRole(['superuser','admin','college_admin','department_admin','director','storekeeper']);
        }
    }

    $currentEmployeeId = old('employee_id', optional($employee)->id);
    $currentRoomNo = old('room_no', optional($employee)->room_no);
@endphp

@push('styles')
<style>
    .required:after { content:' *'; color:#dc3545; font-weight:700; }
    .rr-help { font-size:12px; color:#6c757d; }
    .rr-section-title { font-weight:700; color:#1f7f4c; }
    .rr-readonly { background:#f8f9fa; }
</style>
@endpush

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">New Repair / Material Request</h4>
        <small class="text-muted">Request will be submitted to Storekeeper first.</small>
    </div>
    <a href="{{ route('repair-requests.index') }}" class="btn btn-secondary btn-sm">Back</a>
</div>

<div class="alert alert-info">
    Select your allocated asset if available. Item details will be filled automatically and locked.
    If the asset is not listed, choose <strong>Asset not listed / General request</strong> and fill minimum details.
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('repair-requests.store') }}" enctype="multipart/form-data" id="repairRequestForm">
            @csrf

            @if($canSelectEmployee)
                <div class="form-group">
                    <label class="required">Employee</label>
                    <select name="employee_id" id="request_employee_id" class="form-control" required>
                        <option value="">Select Employee</option>
                        @foreach($employees as $e)
                            <option value="{{ $e->id }}" {{ (string)$currentEmployeeId === (string)$e->id ? 'selected' : '' }}>
                                {{ $e->display_name }}{{ $e->department ? ' - '.$e->department->name : '' }}
                            </option>
                        @endforeach
                    </select>
                    <small class="rr-help">Select the employee for whom this request is being submitted.</small>
                </div>
            @else
                {{-- IMPORTANT: name="employee_id" is required so the controller receives the logged-in employee. --}}
                <input type="hidden" name="employee_id" id="request_employee_id" value="{{ $currentEmployeeId }}">
                <div class="alert alert-light border py-2">
                    <strong>Employee:</strong> {{ optional($employee)->display_name ?: 'Logged-in employee' }}
                    @if(optional($employee)->department)
                        <span class="text-muted">({{ $employee->department->name }})</span>
                    @endif
                </div>
            @endif

            <div class="row">
                <div class="col-md-12 mb-2">
                    <div class="rr-section-title">Request Details</div>
                </div>

                <div class="col-md-5 form-group">
                    <label>Allocated Asset</label>
                    <select name="asset_id" id="asset_id" class="form-control">
                        <option value="">Asset not listed / General request</option>
                        @foreach($assets as $a)
                            <option value="{{ $a->id }}" data-category="{{ $a->asset_category }}" {{ old('asset_id') == $a->id ? 'selected' : '' }}>
                                {{ $a->inventory_no ?: $a->asset_code }} - {{ $a->item_name }} {{ $a->make ? '('.$a->make.' '.$a->model.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <small class="rr-help">Employees see only their allocated assets. Storekeeper/Admin can select employee first.</small>
                </div>

                <div class="col-md-4 form-group">
                    <label class="required">Category</label>
                    <select name="repair_category_id" id="repair_category_id" class="form-control" required>
                        <option value="">Select</option>
                        @foreach($categories as $c)
                            <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-group="{{ $c->item_group }}" {{ old('repair_category_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }} - {{ $c->item_group }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label class="required">Priority</label>
                    <select name="priority" class="form-control" required>
                        <option value="Normal" {{ old('priority', 'Normal') == 'Normal' ? 'selected' : '' }}>Normal</option>
                        <option value="Urgent" {{ old('priority') == 'Urgent' ? 'selected' : '' }}>Urgent</option>
                    </select>
                </div>

                <div class="col-md-4 form-group">
                    <label class="required">Default Problem / Requirement</label>
                    <select name="problem_template_id" id="problem_template_id" class="form-control" required>
                        <option value="">First select category</option>
                    </select>
                    <small class="rr-help">Mandatory. This list changes automatically according to selected category.</small>
                </div>

                <div class="col-md-2 form-group asset-fields">
                    <label>Item Type</label>
                    <input name="item_type" id="item_type" class="form-control" value="{{ old('item_type') }}" placeholder="Auto/Manual">
                </div>

                <div class="col-md-3 form-group asset-fields">
                    <label>Item Name</label>
                    <input name="item_name" id="item_name" class="form-control" value="{{ old('item_name') }}" placeholder="Auto/Manual">
                </div>

                <div class="col-md-3 form-group asset-fields">
                    <label>Inventory No.</label>
                    <input name="inventory_no" id="inventory_no" class="form-control" value="{{ old('inventory_no') }}" placeholder="Auto if asset selected">
                </div>

                <div class="col-md-2 form-group">
                    <label>Room No.</label>
                    <input name="room_no" id="room_no" class="form-control" value="{{ $currentRoomNo }}" placeholder="Room/Location">
                </div>

                <div class="col-md-12 form-group">
                    <label>Problem / Material Requirement</label>
                    <textarea name="problem_description" id="problem_description" class="form-control" rows="4" placeholder="This will be filled from default problem. You may add more details if asset is not listed.">{{ old('problem_description') }}</textarea>
                    <small class="rr-help">On selecting a default problem, description will be filled automatically. You may edit/add details.</small>
                </div>

                <div class="col-md-4 form-group">
                    <label>Photo/PDF Attachment</label>
                    <input type="file" name="attachment" class="form-control-file" accept=".jpg,.jpeg,.png,.pdf">
                    <small class="rr-help">Allowed: JPG, JPEG, PNG, PDF.</small>
                </div>
            </div>

            <button type="submit" class="btn btn-success">Submit Request to Storekeeper</button>
            <a href="{{ route('repair-requests.index') }}" class="btn btn-secondary">Cancel</a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function($){
    var baseUrl = "{{ url('/') }}";
    var canSelectEmployee = @json($canSelectEmployee);
    var defaultRoomNo = @json($currentRoomNo);
    var oldEmployee = @json(old('employee_id', $currentEmployeeId));
    var oldCategory = @json(old('repair_category_id'));
    var oldProblem = @json(old('problem_template_id'));
    var oldAsset = @json(old('asset_id'));

    function lockAssetFields(isLocked) {
        $('#item_type, #item_name, #inventory_no, #room_no')
            .prop('readonly', isLocked)
            .toggleClass('rr-readonly bg-light', isLocked);
    }

    function clearAssetFields() {
        $('#item_type').val('');
        $('#item_name').val('');
        $('#inventory_no').val('');
        $('#room_no').val(defaultRoomNo || '');
        lockAssetFields(false);
    }

    function setCategoryByName(name) {
        if (!name) return;
        var selectedValue = '';
        $('#repair_category_id option').each(function(){
            var optionName = String($(this).data('name') || '').toLowerCase();
            var optionGroup = String($(this).data('group') || '').toLowerCase();
            var inputName = String(name || '').toLowerCase();

            if (optionName === inputName) {
                selectedValue = $(this).val();
                return false;
            }
            if (!selectedValue && inputName === 'computer' && optionGroup === 'computer related') {
                selectedValue = $(this).val();
            }
        });

        if (selectedValue) {
            $('#repair_category_id').val(selectedValue).trigger('change');
        }
    }

    function fillAsset(asset) {
        $('#item_type').val(asset.item_type || '');
        $('#item_name').val(asset.item_name || '');
        $('#inventory_no').val(asset.inventory_no || '');
        $('#room_no').val(asset.room_no || defaultRoomNo || '');
        lockAssetFields(true);
        setCategoryByName(asset.suggested_category_name || asset.asset_category);
    }

    function loadAssetsForEmployee(employeeId, selectedAssetId) {
        $('#asset_id').html('<option value="">Asset not listed / General request</option>');
        clearAssetFields();
        if (!employeeId) return;

        $.get(baseUrl + '/assets/by-employee/' + employeeId)
            .done(function(assets){
                assets.forEach(function(a){
                    var option = $('<option>', {value:a.id, text:a.label});
                    if (selectedAssetId && String(selectedAssetId) === String(a.id)) {
                        option.prop('selected', true);
                    }
                    $('#asset_id').append(option);
                });
                if (selectedAssetId) {
                    $('#asset_id').trigger('change');
                }
            })
            .fail(function(){
                alert('Unable to load allocated assets for selected employee.');
            });
    }

    function loadProblemTemplates(categoryId, selectedProblemId) {
        var $problem = $('#problem_template_id');
        $problem.html('<option value="">Loading problems...</option>');

        if (!categoryId) {
            $problem.html('<option value="">First select category</option>');
            return;
        }

        $.get(baseUrl + '/repair-categories/' + categoryId + '/problem-templates')
            .done(function(items){
                $problem.html('<option value="">Select default problem</option>');
                items.forEach(function(item){
                    var description = item.description || item.title;
                    $problem.append(
                        $('<option>', {value:item.id, text:item.title}).attr('data-description', description)
                    );
                });

                if (selectedProblemId) {
                    $problem.val(selectedProblemId);
                }
                $problem.trigger('change');
            })
            .fail(function(){
                $problem.html('<option value="">Unable to load problems</option>');
                alert('Default Problem / Requirement could not be loaded. Please check route and local server.');
            });
    }

    $('#request_employee_id').on('change', function(){
        if (canSelectEmployee) {
            loadAssetsForEmployee($(this).val(), null);
        }
    });

    $('#asset_id').on('change', function(){
        var id = $(this).val();
        if (!id) {
            clearAssetFields();
            return;
        }
        $.get(baseUrl + '/assets/' + id + '/json')
            .done(function(asset){ fillAsset(asset); })
            .fail(function(){ alert('Unable to load selected asset details.'); });
    });

    $('#repair_category_id').on('change', function(){
        loadProblemTemplates($(this).val(), null);
    });

    $('#problem_template_id').on('change', function(){
        var desc = $(this).find('option:selected').attr('data-description') || '';
        if (desc && !@json((bool) old('problem_description'))) {
            $('#problem_description').val(desc);
        } else if (desc && $('#problem_description').val().trim() === '') {
            $('#problem_description').val(desc);
        }
    });

    $(document).ready(function(){
        lockAssetFields(false);

        if (canSelectEmployee && oldEmployee) {
            $('#request_employee_id').val(oldEmployee);
            loadAssetsForEmployee(oldEmployee, oldAsset);
        } else if (oldAsset) {
            $('#asset_id').val(oldAsset).trigger('change');
        }

        if (oldCategory) {
            $('#repair_category_id').val(oldCategory);
            loadProblemTemplates(oldCategory, oldProblem);
        } else if ($('#repair_category_id').val()) {
            loadProblemTemplates($('#repair_category_id').val(), oldProblem);
        }
    });
})(jQuery);
</script>
@endpush
