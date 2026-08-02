@extends('layouts.app')

@section('content')
@php
    $loggedUser = Auth::user();
    $canSelectEmployee = false;

    if ($loggedUser) {
        if (method_exists($loggedUser, 'hasAnyRole')) {
            $canSelectEmployee = $loggedUser->hasAnyRole([
                'superuser',
                'admin',
                'college_admin',
                'department_admin',
                'director',
                'storekeeper'
            ]);
        } elseif (method_exists($loggedUser, 'isRole')) {
            $canSelectEmployee = $loggedUser->isRole([
                'superuser',
                'admin',
                'college_admin',
                'department_admin',
                'director',
                'storekeeper'
            ]);
        }
    }

    $currentEmployeeId = old('employee_id', optional($employee)->id);
    $currentRoomNo = old('room_no', optional($employee)->room_no);
@endphp

@push('styles')
<style>
    .required:after {
        content: ' *';
        color: #dc3545;
        font-weight: 700;
    }

    .rr-help {
        font-size: 12px;
        color: #6c757d;
    }

    .rr-section-title {
        font-weight: 700;
        color: #1f7f4c;
    }

    .rr-readonly {
        background: #f8f9fa;
    }
</style>
@endpush

<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">ਨਵੀਂ ਮੁਰੰਮਤ / ਸਮੱਗਰੀ ਬੇਨਤੀ</h4>
        <small class="text-muted">
            ਬੇਨਤੀ ਸਭ ਤੋਂ ਪਹਿਲਾਂ ਸਟੋਰ ਕੀਪਰ ਨੂੰ ਭੇਜੀ ਜਾਵੇਗੀ।
        </small>
    </div>

    <a href="{{ route('repair-requests.index') }}"
       class="btn btn-secondary btn-sm">
        ਵਾਪਸ
    </a>
</div>

<div class="alert alert-info">
    ਜੇਕਰ ਤੁਹਾਨੂੰ ਅਲਾਟ ਕੀਤੀ ਸੰਪਤੀ ਸੂਚੀ ਵਿੱਚ ਉਪਲਬਧ ਹੈ ਤਾਂ ਉਸਦੀ ਚੋਣ ਕਰੋ।
    ਸੰਪਤੀ ਨਾਲ ਸੰਬੰਧਿਤ ਵੇਰਵੇ ਆਪਣੇ ਆਪ ਭਰ ਕੇ ਲੌਕ ਹੋ ਜਾਣਗੇ।
    ਜੇਕਰ ਸੰਪਤੀ ਸੂਚੀ ਵਿੱਚ ਨਹੀਂ ਹੈ ਤਾਂ
    <strong>ਸੰਪਤੀ ਸੂਚੀ ਵਿੱਚ ਨਹੀਂ / ਆਮ ਬੇਨਤੀ</strong>
    ਦੀ ਚੋਣ ਕਰੋ ਅਤੇ ਲੋੜੀਂਦੇ ਘੱਟੋ-ਘੱਟ ਵੇਰਵੇ ਭਰੋ।
</div>

<div class="card">
    <div class="card-body">
        <form method="POST"
              action="{{ route('repair-requests.store') }}"
              enctype="multipart/form-data"
              id="repairRequestForm">

            @csrf

            @if($canSelectEmployee)
                <div class="form-group">
                    <label class="required">ਕਰਮਚਾਰੀ</label>

                    <select name="employee_id"
                            id="request_employee_id"
                            class="form-control"
                            required>

                        <option value="">ਕਰਮਚਾਰੀ ਚੁਣੋ</option>

                        @foreach($employees as $e)
                            <option value="{{ $e->id }}"
                                {{ (string) $currentEmployeeId === (string) $e->id ? 'selected' : '' }}>

                                {{ $e->display_name }}

                                @if($e->department)
                                    - {{ $e->department->name }}
                                @endif
                            </option>
                        @endforeach
                    </select>

                    <small class="rr-help">
                        ਉਸ ਕਰਮਚਾਰੀ ਦੀ ਚੋਣ ਕਰੋ ਜਿਸ ਲਈ ਇਹ ਬੇਨਤੀ ਭੇਜੀ ਜਾ ਰਹੀ ਹੈ।
                    </small>
                </div>
            @else
                <input type="hidden"
                       name="employee_id"
                       id="request_employee_id"
                       value="{{ $currentEmployeeId }}">

                <div class="alert alert-light border py-2">
                    <strong>ਕਰਮਚਾਰੀ:</strong>
                    {{ optional($employee)->display_name ?: 'ਲੌਗਇਨ ਕੀਤਾ ਕਰਮਚਾਰੀ' }}

                    @if(optional($employee)->department)
                        <span class="text-muted">
                            ({{ $employee->department->name }})
                        </span>
                    @endif
                </div>
            @endif

            <div class="row">
                <div class="col-md-12 mb-2">
                    <div class="rr-section-title">
                        ਬੇਨਤੀ ਦੇ ਵੇਰਵੇ
                    </div>
                </div>

                <div class="col-md-5 form-group">
                    <label>ਅਲਾਟ ਕੀਤੀ ਸੰਪਤੀ</label>

                    <select name="asset_id"
                            id="asset_id"
                            class="form-control">

                        <option value="">
                            ਸੰਪਤੀ ਸੂਚੀ ਵਿੱਚ ਨਹੀਂ / ਆਮ ਬੇਨਤੀ
                        </option>

                        @foreach($assets as $a)
                            <option value="{{ $a->id }}"
                                    data-category="{{ $a->asset_category }}"
                                    {{ old('asset_id') == $a->id ? 'selected' : '' }}>

                                {{ $a->inventory_no ?: $a->asset_code }}
                                -
                                {{ $a->item_name }}

                                @if($a->make)
                                    ({{ trim($a->make.' '.$a->model) }})
                                @endif
                            </option>
                        @endforeach
                    </select>

                    <small class="rr-help">
                        ਕਰਮਚਾਰੀ ਸਿਰਫ਼ ਆਪਣੇ ਨਾਮ ਅਲਾਟ ਕੀਤੀਆਂ ਸੰਪਤੀਆਂ ਵੇਖ ਸਕਦਾ ਹੈ।
                        ਸਟੋਰ ਕੀਪਰ/ਪ੍ਰਸ਼ਾਸਕ ਪਹਿਲਾਂ ਕਰਮਚਾਰੀ ਦੀ ਚੋਣ ਕਰ ਸਕਦਾ ਹੈ।
                    </small>
                </div>

                <div class="col-md-4 form-group">
                    <label class="required">ਸ਼੍ਰੇਣੀ</label>

                    <select name="repair_category_id"
                            id="repair_category_id"
                            class="form-control"
                            required>

                        <option value="">ਸ਼੍ਰੇਣੀ ਚੁਣੋ</option>

                        @foreach($categories as $c)
                            <option value="{{ $c->id }}"
                                    data-name="{{ $c->name }}"
                                    data-group="{{ $c->item_group }}"
                                    {{ old('repair_category_id') == $c->id ? 'selected' : '' }}>

                                {{ $c->name }} - {{ $c->item_group }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label class="required">ਤਰਜੀਹ</label>

                    <select name="priority"
                            class="form-control"
                            required>

                        <option value="Normal"
                            {{ old('priority', 'Normal') == 'Normal' ? 'selected' : '' }}>
                            ਆਮ
                        </option>

                        <option value="Urgent"
                            {{ old('priority') == 'Urgent' ? 'selected' : '' }}>
                            ਤੁਰੰਤ
                        </option>
                    </select>
                </div>

                <div class="col-md-4 form-group">
                    <label class="required">
                        ਮੂਲ ਸਮੱਸਿਆ / ਲੋੜ
                    </label>

                    <select name="problem_template_id"
                            id="problem_template_id"
                            class="form-control"
                            required>

                        <option value="">
                            ਪਹਿਲਾਂ ਸ਼੍ਰੇਣੀ ਚੁਣੋ
                        </option>
                    </select>

                    <small class="rr-help">
                        ਲਾਜ਼ਮੀ। ਚੁਣੀ ਗਈ ਸ਼੍ਰੇਣੀ ਅਨੁਸਾਰ ਸਮੱਸਿਆਵਾਂ ਦੀ ਸੂਚੀ
                        ਆਪਣੇ ਆਪ ਬਦਲੇਗੀ।
                    </small>
                </div>

                <div class="col-md-2 form-group asset-fields">
                    <label>ਵਸਤੂ ਦੀ ਕਿਸਮ</label>

                    <input name="item_type"
                           id="item_type"
                           class="form-control"
                           value="{{ old('item_type') }}"
                           placeholder="ਆਪਣੇ ਆਪ / ਹੱਥੋਂ">
                </div>

                <div class="col-md-3 form-group asset-fields">
                    <label>ਵਸਤੂ ਦਾ ਨਾਮ</label>

                    <input name="item_name"
                           id="item_name"
                           class="form-control"
                           value="{{ old('item_name') }}"
                           placeholder="ਆਪਣੇ ਆਪ / ਹੱਥੋਂ">
                </div>

                <div class="col-md-3 form-group asset-fields">
                    <label>ਇਨਵੈਂਟਰੀ ਨੰਬਰ</label>

                    <input name="inventory_no"
                           id="inventory_no"
                           class="form-control"
                           value="{{ old('inventory_no') }}"
                           placeholder="ਸੰਪਤੀ ਚੁਣਨ ਤੇ ਆਪਣੇ ਆਪ ਭਰੇਗਾ">
                </div>

                <div class="col-md-2 form-group">
                    <label>ਕਮਰਾ ਨੰਬਰ / ਸਥਾਨ</label>

                    <input name="room_no"
                           id="room_no"
                           class="form-control"
                           value="{{ $currentRoomNo }}"
                           placeholder="ਕਮਰਾ / ਸਥਾਨ">
                </div>

                <div class="col-md-12 form-group">
                    <label class="required">
                        ਸਮੱਸਿਆ / ਸਮੱਗਰੀ ਦੀ ਲੋੜ
                    </label>

                    <textarea name="problem_description"
                              id="problem_description"
                              class="form-control"
                              rows="4"
                              required
                              placeholder="ਚੁਣੀ ਗਈ ਮੂਲ ਸਮੱਸਿਆ ਦਾ ਵੇਰਵਾ ਇੱਥੇ ਆਪਣੇ ਆਪ ਆ ਜਾਵੇਗਾ। ਲੋੜ ਅਨੁਸਾਰ ਹੋਰ ਜਾਣਕਾਰੀ ਸ਼ਾਮਲ ਕਰੋ।">{{ old('problem_description') }}</textarea>

                    <small class="rr-help">
                        ਮੂਲ ਸਮੱਸਿਆ ਦੀ ਚੋਣ ਕਰਨ ਤੇ ਵੇਰਵਾ ਆਪਣੇ ਆਪ ਭਰਿਆ ਜਾਵੇਗਾ।
                        ਤੁਸੀਂ ਲੋੜ ਅਨੁਸਾਰ ਇਸ ਵਿੱਚ ਹੋਰ ਜਾਣਕਾਰੀ ਜੋੜ ਸਕਦੇ ਹੋ।
                    </small>
                </div>

                <div class="col-md-4 form-group">
                    <label>ਫੋਟੋ / PDF ਨੱਥੀ ਕਰੋ</label>

                    <input type="file"
                           name="attachment"
                           class="form-control-file"
                           accept=".jpg,.jpeg,.png,.pdf">

                    <small class="rr-help">
                        ਮਨਜ਼ੂਰ ਫਾਈਲਾਂ: JPG, JPEG, PNG ਅਤੇ PDF।
                    </small>
                </div>
            </div>

            <button type="submit" class="btn btn-success">
                ਬੇਨਤੀ ਸਟੋਰ ਕੀਪਰ ਨੂੰ ਭੇਜੋ
            </button>

            <a href="{{ route('repair-requests.index') }}"
               class="btn btn-secondary">
                ਰੱਦ ਕਰੋ
            </a>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function ($) {
    var baseUrl = "{{ url('/') }}";
    var canSelectEmployee = @json($canSelectEmployee);
    var defaultRoomNo = @json($currentRoomNo);
    var oldEmployee = @json(old('employee_id', $currentEmployeeId));
    var oldCategory = @json(old('repair_category_id'));
    var oldProblem = @json(old('problem_template_id'));
    var oldAsset = @json(old('asset_id'));
    var oldProblemDescription = @json(old('problem_description'));

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
        if (!name) {
            return;
        }

        var selectedValue = '';
        var inputName = String(name || '').toLowerCase().trim();

        $('#repair_category_id option').each(function () {
            var optionName = String($(this).data('name') || '')
                .toLowerCase()
                .trim();

            var optionGroup = String($(this).data('group') || '')
                .toLowerCase()
                .trim();

            if (optionName === inputName) {
                selectedValue = $(this).val();
                return false;
            }

            if (
                !selectedValue &&
                inputName === 'computer' &&
                optionGroup === 'computer related'
            ) {
                selectedValue = $(this).val();
            }
        });

        if (selectedValue) {
            $('#repair_category_id')
                .val(selectedValue)
                .trigger('change');
        }
    }

    function fillAsset(asset) {
        $('#item_type').val(asset.item_type || '');
        $('#item_name').val(asset.item_name || '');
        $('#inventory_no').val(asset.inventory_no || '');
        $('#room_no').val(asset.room_no || defaultRoomNo || '');

        lockAssetFields(true);

        setCategoryByName(
            asset.suggested_category_name || asset.asset_category
        );
    }

    function loadAssetsForEmployee(employeeId, selectedAssetId) {
        var $asset = $('#asset_id');

        $asset.html(
            '<option value="">ਸੰਪਤੀ ਸੂਚੀ ਵਿੱਚ ਨਹੀਂ / ਆਮ ਬੇਨਤੀ</option>'
        );

        clearAssetFields();

        if (!employeeId) {
            return;
        }

        $.get(baseUrl + '/assets/by-employee/' + employeeId)
            .done(function (assets) {
                assets.forEach(function (asset) {
                    var option = $('<option>', {
                        value: asset.id,
                        text: asset.label
                    });

                    if (
                        selectedAssetId &&
                        String(selectedAssetId) === String(asset.id)
                    ) {
                        option.prop('selected', true);
                    }

                    $asset.append(option);
                });

                if (selectedAssetId) {
                    $asset.trigger('change');
                }
            })
            .fail(function () {
                alert(
                    'ਚੁਣੇ ਗਏ ਕਰਮਚਾਰੀ ਦੀਆਂ ਅਲਾਟ ਕੀਤੀਆਂ ਸੰਪਤੀਆਂ ਲੋਡ ਨਹੀਂ ਹੋ ਸਕੀਆਂ।'
                );
            });
    }

    function loadProblemTemplates(categoryId, selectedProblemId) {
        var $problem = $('#problem_template_id');

        $problem.html(
            '<option value="">ਸਮੱਸਿਆਵਾਂ ਲੋਡ ਹੋ ਰਹੀਆਂ ਹਨ...</option>'
        );

        if (!categoryId) {
            $problem.html(
                '<option value="">ਪਹਿਲਾਂ ਸ਼੍ਰੇਣੀ ਚੁਣੋ</option>'
            );
            return;
        }

        $.get(
            baseUrl +
            '/repair-categories/' +
            categoryId +
            '/problem-templates'
        )
        .done(function (items) {
            $problem.html(
                '<option value="">ਮੂਲ ਸਮੱਸਿਆ ਚੁਣੋ</option>'
            );

            items.forEach(function (item) {
                var title = item.title || 'ਬਿਨਾਂ ਸਿਰਲੇਖ';
var description = item.description || title;

                $problem.append(
                    $('<option>', {
                        value: item.id,
                        text: title
                    }).attr('data-description', description)
                );
            });

            if (selectedProblemId) {
                $problem.val(String(selectedProblemId));
            }

            $problem.trigger('change');
        })
        .fail(function () {
            $problem.html(
                '<option value="">ਸਮੱਸਿਆਵਾਂ ਲੋਡ ਨਹੀਂ ਹੋ ਸਕੀਆਂ</option>'
            );

            alert(
                'ਮੂਲ ਸਮੱਸਿਆਵਾਂ ਦੀ ਸੂਚੀ ਲੋਡ ਨਹੀਂ ਹੋ ਸਕੀ। ਕਿਰਪਾ ਕਰਕੇ ਰੂਟ ਅਤੇ ਸਰਵਰ ਦੀ ਜਾਂਚ ਕਰੋ।'
            );
        });
    }

    $('#request_employee_id').on('change', function () {
        if (canSelectEmployee) {
            loadAssetsForEmployee($(this).val(), null);
        }
    });

    $('#asset_id').on('change', function () {
        var id = $(this).val();

        if (!id) {
            clearAssetFields();
            return;
        }

        $.get(baseUrl + '/assets/' + id + '/json')
            .done(function (asset) {
                fillAsset(asset);
            })
            .fail(function () {
                alert(
                    'ਚੁਣੀ ਗਈ ਸੰਪਤੀ ਦੇ ਵੇਰਵੇ ਲੋਡ ਨਹੀਂ ਹੋ ਸਕੇ।'
                );
            });
    });

    $('#repair_category_id').on('change', function () {
        loadProblemTemplates($(this).val(), null);
    });

    $('#problem_template_id').on('change', function () {
        var description =
            $(this)
                .find('option:selected')
                .attr('data-description') || '';

        /*
         * Preserve the description returned after validation.
         * Otherwise fill the Punjabi default description.
         */
        if (oldProblemDescription) {
            $('#problem_description').val(oldProblemDescription);
            oldProblemDescription = '';
            return;
        }

        if (description) {
            $('#problem_description').val(description);
        }
    });

    $(document).ready(function () {
        lockAssetFields(false);

        if (canSelectEmployee && oldEmployee) {
            $('#request_employee_id').val(oldEmployee);
            loadAssetsForEmployee(oldEmployee, oldAsset);
        } else if (oldAsset) {
            $('#asset_id')
                .val(oldAsset)
                .trigger('change');
        }

        if (oldCategory) {
            $('#repair_category_id').val(oldCategory);
            loadProblemTemplates(oldCategory, oldProblem);
        } else if ($('#repair_category_id').val()) {
            loadProblemTemplates(
                $('#repair_category_id').val(),
                oldProblem
            );
        }
    });
})(jQuery);
</script>
@endpush