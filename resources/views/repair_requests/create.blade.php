@extends('layouts.app')
@section('content')
<h4>New Repair / Material Request</h4>
<div class="alert alert-info">
    Select your allocated asset if available. Item details will be filled automatically and locked. If the asset is not listed, choose <strong>Asset not listed / General request</strong> and fill only minimum details.
</div>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('repair-requests.store') }}" enctype="multipart/form-data" id="repairRequestForm">
@csrf
@if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))
<div class="form-group">
    <label class="required">Employee</label>
    <select name="employee_id" id="request_employee_id" class="form-control" required>
        <option value="">Select Employee</option>
        @foreach($employees as $e)
            <option value="{{ $e->id }}">{{ $e->display_name }}{{ $e->department ? ' - '.$e->department->name : '' }}</option>
        @endforeach
    </select>
</div>
@else
<input type="hidden" id="request_employee_id" value="{{ optional($employee)->id }}">
@endif

<div class="row">
    <div class="col-md-5 form-group">
        <label>Allocated Asset</label>
        <select name="asset_id" id="asset_id" class="form-control">
            <option value="">Asset not listed / General request</option>
            @foreach($assets as $a)
                <option value="{{ $a->id }}" data-category="{{ $a->asset_category }}">
                    {{ $a->inventory_no ?: $a->asset_code }} - {{ $a->item_name }} {{ $a->make ? '('.$a->make.' '.$a->model.')' : '' }}
                </option>
            @endforeach
        </select>
        <small class="text-muted">Employees see only their allocated assets. Storekeeper/Admin can select employee first.</small>
    </div>

    <div class="col-md-4 form-group">
        <label class="required">Category</label>
        <select name="repair_category_id" id="repair_category_id" class="form-control" required>
            <option value="">Select</option>
            @foreach($categories as $c)
                <option value="{{ $c->id }}" data-name="{{ $c->name }}" data-group="{{ $c->item_group }}">{{ $c->name }} - {{ $c->item_group }}</option>
            @endforeach
        </select>
    </div>

    <div class="col-md-3 form-group">
        <label>Priority</label>
        <select name="priority" class="form-control">
            <option>Normal</option>
            <option>Urgent</option>
        </select>
    </div>

    <div class="col-md-4 form-group">
        <label class="required">Default Problem / Requirement</label>
        <select name="problem_template_id" id="problem_template_id" class="form-control" required>
            <option value="">First select category</option>
        </select>
        <small class="text-muted">Mandatory. This list changes automatically according to selected category.</small>
    </div>

    <div class="col-md-2 form-group asset-fields">
        <label>Item Type</label>
        <input name="item_type" id="item_type" class="form-control" placeholder="Auto/Manual">
    </div>
    <div class="col-md-3 form-group asset-fields">
        <label>Item Name</label>
        <input name="item_name" id="item_name" class="form-control" placeholder="Auto/Manual">
    </div>
    <div class="col-md-3 form-group asset-fields">
        <label>Inventory No.</label>
        <input name="inventory_no" id="inventory_no" class="form-control" placeholder="Auto if asset selected">
    </div>
    <div class="col-md-2 form-group">
        <label>Room No.</label>
        <input name="room_no" id="room_no" class="form-control" value="{{ optional($employee)->room_no }}" placeholder="Room/Location">
    </div>

    <div class="col-md-12 form-group">
        <label>Problem / Material Requirement</label>
        <textarea name="problem_description" id="problem_description" class="form-control" rows="4" placeholder="This will be filled from default problem. You may add more details if asset is not listed."></textarea>
        <small class="text-muted">On selecting a default problem, description will be filled automatically. You may edit/add details.</small>
    </div>

    <div class="col-md-4 form-group">
        <label>Photo/PDF Attachment</label>
        <input type="file" name="attachment" class="form-control-file">
    </div>
</div>
<button class="btn btn-success">Submit Request to Storekeeper</button>
<a href="{{ route('repair-requests.index') }}" class="btn btn-secondary">Cancel</a>
</form></div></div>
@endsection

@push('scripts')
<script>
(function($){
    var baseUrl = "{{ url('/') }}";
    var defaultRoomNo = @json(optional($employee)->room_no);

    function lockAssetFields(isLocked) {
        $('#item_type, #item_name, #inventory_no, #room_no')
            .prop('readonly', isLocked)
            .toggleClass('bg-light', isLocked);
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

    function loadAssetsForEmployee(employeeId) {
        $('#asset_id').html('<option value="">Asset not listed / General request</option>');
        clearAssetFields();
        if (!employeeId) return;

        $.get(baseUrl + '/assets/by-employee/' + employeeId)
            .done(function(assets){
                assets.forEach(function(a){
                    $('#asset_id').append($('<option>', {value:a.id, text:a.label}));
                });
            })
            .fail(function(){
                alert('Unable to load allocated assets for selected employee.');
            });
    }

    function loadProblemTemplates(categoryId, selectedProblemId) {
        var $problem = $('#problem_template_id');
        $problem.html('<option value="">Loading problems...</option>');
        $('#problem_description').val('');

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
                alert('Default Problem / Requirement could not be loaded. Please check route and internet/local server.');
            });
    }

    $('#request_employee_id').on('change', function(){
        @if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))
            loadAssetsForEmployee($(this).val());
        @endif
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
        loadProblemTemplates($(this).val());
    });

    $('#problem_template_id').on('change', function(){
        var desc = $(this).find('option:selected').attr('data-description') || '';
        $('#problem_description').val(desc);
    });

    // If validation sends user back with old values, reload templates and lock fields if needed.
    $(document).ready(function(){
        lockAssetFields(false);
        var oldCategory = @json(old('repair_category_id'));
        var oldProblem = @json(old('problem_template_id'));
        var oldAsset = @json(old('asset_id'));

        if (oldCategory) {
            $('#repair_category_id').val(oldCategory);
            loadProblemTemplates(oldCategory, oldProblem);
        }
        if (oldAsset) {
            $('#asset_id').val(oldAsset).trigger('change');
        }
    });
})(jQuery);
</script>
@endpush
