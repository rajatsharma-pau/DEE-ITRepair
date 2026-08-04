@php
    $authUser = Auth::user();

    /*
     * The create page may include this form without passing an $employee
     * variable. Use a blank Employee model so all shared create/edit fields
     * can be accessed safely.
     */
    if (!isset($employee) || !$employee) {
        $employee = new \App\Employee();
    }

    $isEditMode = (bool) $employee->exists;

    $safeDate = function ($value) {
        if (empty($value)) {
            return '';
        }

        try {
            if (is_object($value) && method_exists($value, 'format')) {
                return $value->format('Y-m-d');
            }

            return date('Y-m-d', strtotime($value));
        } catch (\Exception $e) {
            return '';
        }
    };

    /*
     * Robust role check. This supports:
     * - AccessScope role normalization
     * - hasAnyRole()
     * - hasRole()
     * - isRole()
     * - legacy users.role column
     */
    $hasAnyRole = function ($roles) use ($authUser) {
        if (!$authUser) {
            return false;
        }

        $roles = is_array($roles) ? $roles : [$roles];

        if (
            method_exists($authUser, 'hasAnyRole')
            && $authUser->hasAnyRole($roles)
        ) {
            return true;
        }

        foreach ($roles as $role) {
            if (
                method_exists($authUser, 'hasRole')
                && $authUser->hasRole($role)
            ) {
                return true;
            }

            if (
                method_exists($authUser, 'isRole')
                && (
                    $authUser->isRole($role)
                    || $authUser->isRole([$role])
                )
            ) {
                return true;
            }

            if (
                isset($authUser->role)
                && \App\Support\AccessScope::normalizeRole($authUser->role)
                    === \App\Support\AccessScope::normalizeRole($role)
            ) {
                return true;
            }
        }

        return false;
    };

    $isSuperuser =
        $authUser
        && \App\Support\AccessScope::isSuperuser($authUser);

    $isCollegeLevelAdmin = $hasAnyRole([
        'admin',
        'college_admin',
        'director'
    ]);

    $isDepartmentAdmin = $hasAnyRole([
        'department_admin'
    ]);

    $scopeCollegeId = $authUser
        ? \App\Support\AccessScope::collegeId($authUser)
        : null;

    $scopeDepartmentId = $authUser
        ? \App\Support\AccessScope::departmentId($authUser)
        : null;

    /*
     * IMPORTANT:
     * On CREATE, Superuser must not inherit their own employee posting.
     * On EDIT, use the employee being edited.
     */
    if ($isEditMode) {
        $defaultCollegeId = $employee->college_id;
        $defaultDepartmentId = $employee->department_id;
    } elseif ($isSuperuser) {
        $defaultCollegeId = null;
        $defaultDepartmentId = null;
    } else {
        $defaultCollegeId = $scopeCollegeId;
        $defaultDepartmentId = $scopeDepartmentId;
    }

    $selectedCollegeId = old('college_id', $defaultCollegeId);
    $selectedDepartmentId = old('department_id', $defaultDepartmentId);

    /*
     * Permission to choose posting.
     */
    $canChooseCollege = $isSuperuser;
    $canChooseDepartment = $isSuperuser || $isCollegeLevelAdmin;

    if ($isDepartmentAdmin) {
        $canChooseCollege = false;
        $canChooseDepartment = false;
    }

    $roleOptions = \App\Support\AccessScope::roleOptions(
        isset($employee) ? $employee : null,
        $authUser
    );

    $selectedRoles = old('roles', []);

    if (
        empty($selectedRoles)
        && $isEditMode
        && $employee->user
        && method_exists($employee->user, 'roleNames')
    ) {
        $selectedRoles = $employee->user->roleNames();
    }

    if (empty($selectedRoles)) {
        $selectedRoles = ['employee'];
    }

    if (!is_array($selectedRoles)) {
        $selectedRoles = collect($selectedRoles)->toArray();
    }

    $selectedRoles = array_map(function ($role) {
        return \App\Support\AccessScope::normalizeRole($role);
    }, $selectedRoles);

    $scopeText = $isSuperuser
        ? 'Scope: All PAU / University'
        : \App\Support\AccessScope::scopeLabel($authUser);
@endphp

@push('styles')
<style>
    .employee-form .card { border:0; border-radius:10px; box-shadow:0 2px 10px rgba(0,0,0,.07); }
    .employee-form .card-header { background:#f8fafc; font-weight:700; border-bottom:1px solid #e9ecef; }
    .employee-form .section-help { font-size:12px; color:#6c757d; font-weight:400; margin-left:6px; }
    .employee-form label.required:after { content:' *'; color:#dc3545; font-weight:800; }
    .employee-form .form-control:focus { border-color:#1f7f4c; box-shadow:0 0 0 .12rem rgba(31,127,76,.18); }
    .employee-form .field-help { font-size:12px; color:#6c757d; display:block; margin-top:3px; }
    .employee-form .scope-box { border-left:4px solid #1f7f4c; background:#eef9f3; padding:10px 12px; border-radius:6px; color:#235c43; }
    .employee-form .scope-locked { background:#f1f3f5; cursor:not-allowed; }
    .employee-form .photo-thumb { width:74px; height:74px; object-fit:cover; border-radius:8px; border:1px solid #dee2e6; }
    .employee-form .small-title { font-size:13px; text-transform:uppercase; letter-spacing:.04em; color:#6c757d; margin-bottom:8px; font-weight:700; }
    .employee-form .mandatory-note { background:#fff6f6; border:1px solid #ffd7d7; color:#8a1f1f; border-radius:6px; padding:8px 10px; font-size:13px; }
</style>
@endpush

<div class="employee-form">
    <div class="mandatory-note mb-3">
        Fields marked with <strong class="text-danger">*</strong> are mandatory. Keep official designation separate from system roles.
    </div>

    <div class="scope-box mb-3">
        <strong>{{ $scopeText }}</strong><br>
        <small>
            @if($isSuperuser)
                Superuser can select any College/Directorate and any Department. The Superuser's own employee posting does not restrict this form.
            @else
                College / Department is filled according to your assigned scope. Department Admin is locked to the assigned department.
            @endif
        </small>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            Login & Basic Details
            <span class="section-help">Phone number is the login ID. One employee can have multiple system roles.</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-2 form-group">
                    <label>Salutation</label>
                    <select name="salutation" class="form-control">
                        <option value="">Select</option>
                        @foreach(['Mr.','Mrs.','Ms.','Dr.','Er.','Prof.'] as $s)
                            <option value="{{ $s }}" {{ old('salutation', $employee->salutation)==$s ? 'selected' : '' }}>{{ $s }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label class="required">First Name</label>
                    <input name="first_name" class="form-control" value="{{ old('first_name', $employee->first_name) }}" required maxlength="100">
                </div>

                <div class="col-md-3 form-group">
                    <label>Middle Name</label>
                    <input name="middle_name" class="form-control" value="{{ old('middle_name', $employee->middle_name) }}" maxlength="100">
                </div>

                <div class="col-md-4 form-group">
                    <label>Last Name</label>
                    <input name="last_name" class="form-control" value="{{ old('last_name', $employee->last_name) }}" maxlength="100">
                </div>

                <div class="col-md-3 form-group">
                    <label class="required">Phone / Login ID</label>
                    <input name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}" required maxlength="20" inputmode="numeric">
                    <small class="field-help">This phone number is used for login and OTP.</small>
                </div>

                <div class="col-md-3 form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" value="{{ old('email', $employee->email) }}" maxlength="150">
                </div>

                <div class="col-md-3 form-group">
                    <label class="{{ $employee->exists ? '' : 'required' }}">Password {{ $employee->exists ? '(blank = no change)' : '' }}</label>
                    <input type="password" name="password" class="form-control" {{ $employee->exists ? '' : 'required' }} minlength="6" autocomplete="new-password">
                    <small class="field-help">For edit page, leave blank if password should not change.</small>
                </div>

                <div class="col-md-3 form-group">
                    <label class="required">Roles</label>
                    <select name="roles[]" class="form-control" multiple size="6" required>
                        @foreach($roleOptions as $key => $value)
                            @php
                                if (is_object($value)) {
                                    $roleSlug = $value->slug ?: $value->name;
                                    $roleLabel = $value->display_name ?: ucwords(str_replace('_', ' ', $roleSlug));
                                } else {
                                    $roleSlug = is_string($key) ? $key : $value;
                                    $roleLabel = ucwords(str_replace('_', ' ', $value));
                                }
                            @endphp
                            <option value="{{ $roleSlug }}" {{ in_array($roleSlug, $selectedRoles) ? 'selected' : '' }}>
                                {{ $roleLabel }}
                            </option>
                        @endforeach
                    </select>
                    <small class="field-help">Use Ctrl key to select multiple roles. Example: Employee + Storekeeper.</small>
                </div>

                <div class="col-md-3 form-group">
                    <label>Photo</label>
                    <input type="file" name="photo" class="form-control-file" accept="image/*">
                    <small class="field-help">Recommended small passport-size image.</small>
                    @if($employee->photo_url)
                        <img src="{{ $employee->photo_url }}" class="photo-thumb mt-2" alt="Employee Photo">
                    @endif
                </div>

                <div class="col-md-3 form-group pt-4">
                    <label class="mb-0">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', optional($employee->user)->is_active ?? true) ? 'checked' : '' }}>
                        Login Active
                    </label>
                    <small class="field-help">Uncheck only when login should be blocked.</small>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            Service Details
            <span class="section-help">Official posting, designation and job information.</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>Employee Code</label>
                    <input name="employee_code" class="form-control" value="{{ old('employee_code', $employee->employee_code) }}" maxlength="50">
                </div>

                <div class="col-md-3 form-group">
                    <label class="required">College / Directorate</label>

                    @if(!$canChooseCollege)
                        <input type="hidden" name="college_id" value="{{ $selectedCollegeId }}">
                    @endif

                    <select id="college_id" name="{{ $canChooseCollege ? 'college_id' : 'college_id_disabled' }}" class="form-control {{ $canChooseCollege ? '' : 'scope-locked' }}" {{ $canChooseCollege ? '' : 'disabled' }} required>
                        <option value="">Select</option>
                        @foreach($colleges as $c)
                            <option value="{{ $c->id }}" {{ (string)$selectedCollegeId === (string)$c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                    <small class="field-help">For DEE, select Directorate of Extension Education from Colleges table.</small>
                </div>

                <div class="col-md-3 form-group">
                    <label class="required">Department / Office / KVK</label>

                    @if(!$canChooseDepartment)
                        <input type="hidden" name="department_id" value="{{ $selectedDepartmentId }}">
                    @endif

                    <select id="department_id" name="{{ $canChooseDepartment ? 'department_id' : 'department_id_disabled' }}" class="form-control {{ $canChooseDepartment ? '' : 'scope-locked' }}" {{ $canChooseDepartment ? '' : 'disabled' }} required>
                        <option value="">Select</option>
                        @foreach($departments as $d)
                            <option value="{{ $d->id }}" data-college="{{ $d->college_id }}" {{ (string)$selectedDepartmentId === (string)$d->id ? 'selected' : '' }}>
                                {{ $d->name }}{{ $d->place ? ' - '.$d->place : '' }}
                            </option>
                        @endforeach
                    </select>
                    <small class="field-help">Department Admin is locked to own department.</small>
                </div>

                <div class="col-md-3 form-group">
                    <label>Internal Section</label>
                    <select name="section_id" class="form-control">
                        <option value="">Select</option>
                        @foreach($sections as $s)
                            <option value="{{ $s->id }}" {{ old('section_id', $employee->section_id)==$s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Room No.</label>
                    <input name="room_no" class="form-control" value="{{ old('room_no', $employee->room_no) }}" maxlength="50">
                </div>

                <div class="col-md-4 form-group">
                    <label>Designation Master</label>
                    <select name="designation_id" class="form-control">
                        <option value="">Select</option>
                        @foreach($designations as $d)
                            <option value="{{ $d->id }}" {{ old('designation_id', $employee->designation_id)==$d->id ? 'selected' : '' }}>
                                {{ $d->name }} {{ $d->cadre ? '('.$d->cadre.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                    <small class="field-help">Choose official designation. If not available, use manual designation.</small>
                </div>

                <div class="col-md-4 form-group">
                    <label>Manual Designation</label>
                    <input name="manual_designation" class="form-control" value="{{ old('manual_designation', $employee->manual_designation) }}" placeholder="Use only if not in master" maxlength="150">
                    <small class="field-help">Designation is official post. Role is software permission.</small>
                </div>

                <div class="col-md-4 form-group">
                    <label class="required">Job Type</label>
                    <select name="job_type" class="form-control" required>
                        <option value="">Select</option>
                        @foreach(['Permanent','Adhoc','Temporary','Daily Wages'] as $j)
                            <option value="{{ $j }}" {{ old('job_type', $employee->job_type)==$j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label class="required">DOB</label>
                    <input type="date" id="dob" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', $safeDate($employee->date_of_birth)) }}" required>
                </div>

                <div class="col-md-3 form-group">
                    <label class="required">DOJ</label>
                    <input type="date" id="doj" name="date_of_joining" class="form-control" value="{{ old('date_of_joining', $safeDate($employee->date_of_joining)) }}" required>
                </div>

                <div class="col-md-2 form-group">
                    <label>Retirement Age</label>
                    <input type="number" id="retirement_age" name="retirement_age" class="form-control" value="{{ old('retirement_age', $employee->retirement_age ?: 60) }}" min="18" max="75">
                </div>

                <div class="col-md-4 form-group">
                    <label>Manual Retirement Date</label>
                    <input type="date" name="manual_retirement_date" class="form-control" value="{{ old('manual_retirement_date', $safeDate($employee->manual_retirement_date)) }}">
                    <small class="field-help">Auto date can be calculated from DOB; manual date will override.</small>
                </div>

                <div class="col-md-4 form-group">
                    <label>Manual Annual Increment Date</label>
                    <input type="date" name="manual_increment_date" class="form-control" value="{{ old('manual_increment_date', $safeDate($employee->manual_increment_date)) }}">
                </div>

                <div class="col-md-4 form-group">
                    <label>Increment Remarks</label>
                    <input name="increment_remarks" class="form-control" value="{{ old('increment_remarks', $employee->increment_remarks) }}" maxlength="255">
                </div>

                <div class="col-md-4 form-group">
                    <label class="required">Status</label>
                    <select name="status" class="form-control" required>
                        @foreach(['Active','Retired','Transferred','Inactive'] as $st)
                            <option value="{{ $st }}" {{ old('status', $employee->status ?: 'Active')==$st ? 'selected' : '' }}>{{ $st }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            Identity & Salary Details
            <span class="section-help">Keep official numbers as per service record.</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3 form-group">
                    <label>GPF No.</label>
                    <input name="gpf_no" class="form-control" value="{{ old('gpf_no', $employee->gpf_no) }}" maxlength="50">
                </div>
                <div class="col-md-3 form-group">
                    <label>NPS No.</label>
                    <input name="nps_no" class="form-control" value="{{ old('nps_no', $employee->nps_no) }}" maxlength="50">
                </div>
                <div class="col-md-3 form-group">
                    <label>PAN No.</label>
                    <input name="pan_no" class="form-control text-uppercase" value="{{ old('pan_no', $employee->pan_no) }}" maxlength="10">
                </div>
                <div class="col-md-3 form-group">
                    <label>Aadhaar No.</label>
                    <input name="aadhaar_no" class="form-control" value="{{ old('aadhaar_no', $employee->aadhaar_no) }}" maxlength="12" inputmode="numeric">
                </div>
                <div class="col-md-4 form-group">
                    <label>Salary Account No.</label>
                    <input name="salary_account_no" class="form-control" value="{{ old('salary_account_no', $employee->salary_account_no) }}" maxlength="40">
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header">
            Address
            <span class="section-help">PIN can auto-fill city/state/country when service is available.</span>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6 form-group">
                    <label>Address Line 1</label>
                    <input name="address_line_1" class="form-control" value="{{ old('address_line_1', $employee->address_line_1) }}" maxlength="255">
                </div>
                <div class="col-md-6 form-group">
                    <label>Address Line 2</label>
                    <input name="address_line_2" class="form-control" value="{{ old('address_line_2', $employee->address_line_2) }}" maxlength="255">
                </div>
                <div class="col-md-3 form-group">
                    <label>PIN / ZIP</label>
                    <input id="zip" name="zip" class="form-control" value="{{ old('zip', $employee->zip) }}" maxlength="10" inputmode="numeric">
                    <small id="pin-msg" class="field-help"></small>
                </div>
                <div class="col-md-3 form-group">
                    <label>City</label>
                    <input id="manual_city" name="manual_city" class="form-control" value="{{ old('manual_city', $employee->manual_city) }}" maxlength="100">
                </div>
                <div class="col-md-3 form-group">
                    <label>State</label>
                    <input id="manual_state" name="manual_state" class="form-control" value="{{ old('manual_state', $employee->manual_state) }}" maxlength="100">
                </div>
                <div class="col-md-3 form-group">
                    <label>Country</label>
                    <input id="manual_country" name="manual_country" class="form-control" value="{{ old('manual_country', $employee->manual_country ?: 'India') }}" maxlength="100">
                </div>
                <div class="col-md-12 form-group">
                    <label>Remarks</label>
                    <textarea name="remarks" class="form-control" rows="3">{{ old('remarks', $employee->remarks) }}</textarea>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
var departmentSelect = $('#department_id');
var allDepartmentOptions = departmentSelect.find('option').clone();
var initialDepartmentId = @json($selectedDepartmentId);

function filterDepartments(preserveDepartmentId) {
    var collegeId = String($('#college_id').val() || '');

    departmentSelect.empty();

    allDepartmentOptions.each(function () {
        var option = $(this).clone();
        var optionValue = String(option.val() || '');
        var optionCollege = String(option.data('college') || '');

        if (
            optionValue === ''
            || collegeId === ''
            || optionCollege === collegeId
        ) {
            departmentSelect.append(option);
        }
    });

    if (
        preserveDepartmentId
        && departmentSelect.find(
            'option[value="' + preserveDepartmentId + '"]'
        ).length
    ) {
        departmentSelect.val(String(preserveDepartmentId));
    } else {
        departmentSelect.val('');
    }
}

$('#college_id').on('change', function () {
    filterDepartments(null);
});

filterDepartments(initialDepartmentId);

$('#zip').on('blur', function(){
    var pin = $(this).val();
    if(pin.length === 6){
        $('#pin-msg').text('Checking PIN...').css('color','#6c757d');
        $.get('{{ url('/pincode') }}/' + pin, function(res){
            if(res.success){
                $('#manual_city').val(res.city);
                $('#manual_state').val(res.state);
                $('#manual_country').val(res.country);
                $('#pin-msg').text('Auto-filled from PIN.').css('color','green');
            } else {
                $('#pin-msg').text(res.message).css('color','red');
            }
        }).fail(function(){
            $('#pin-msg').text('PIN auto-fill service not available right now.').css('color','#6c757d');
        });
    }
});
</script>
@endpush