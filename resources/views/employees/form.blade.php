@php($roleOptions = \App\Support\AccessScope::roleOptions())
@php($selectedRoles = old('roles', $employee->user ? $employee->user->roleNames() : ['employee']))
<div class="card mb-3"><div class="card-header">Login & Basic Details</div><div class="card-body">
<div class="row">
    <div class="col-md-2 form-group"><label>Salutation</label><select name="salutation" class="form-control"><option value="">Select</option>@foreach(['Mr.','Mrs.','Ms.','Dr.','Er.','Prof.'] as $s)<option value="{{ $s }}" {{ old('salutation', $employee->salutation)==$s?'selected':'' }}>{{ $s }}</option>@endforeach</select></div>
    <div class="col-md-3 form-group"><label class="required">First Name</label><input name="first_name" class="form-control" value="{{ old('first_name', $employee->first_name) }}" required></div>
    <div class="col-md-3 form-group"><label>Middle Name</label><input name="middle_name" class="form-control" value="{{ old('middle_name', $employee->middle_name) }}"></div>
    <div class="col-md-4 form-group"><label>Last Name</label><input name="last_name" class="form-control" value="{{ old('last_name', $employee->last_name) }}"></div>
    <div class="col-md-3 form-group"><label>Phone/Login ID</label><input name="phone" class="form-control" value="{{ old('phone', $employee->phone) }}"></div>
    <div class="col-md-3 form-group"><label>Email</label><input name="email" class="form-control" value="{{ old('email', $employee->email) }}"></div>
    <div class="col-md-3 form-group"><label>Password {{ $employee->exists ? '(blank = no change)' : '' }}</label><input type="password" name="password" class="form-control"></div>
    <div class="col-md-3 form-group"><label>Roles</label><select name="roles[]" class="form-control" multiple size="4">@foreach($roleOptions as $r)<option value="{{ $r }}" {{ in_array($r, $selectedRoles) ? 'selected':'' }}>{{ ucwords(str_replace('_',' ', $r)) }}</option>@endforeach</select><small>One phone login can have multiple roles. Example: Admin + Storekeeper.</small></div>
    <div class="col-md-3 form-group"><label>Photo</label><input type="file" name="photo" class="form-control-file">@if($employee->photo_url)<img src="{{ $employee->photo_url }}" class="photo-thumb mt-2">@endif</div>
    <div class="col-md-3 form-group pt-4"><label><input type="checkbox" name="is_active" value="1" {{ old('is_active', optional($employee->user)->is_active ?? true) ? 'checked':'' }}> Login Active</label></div>
</div>
</div></div>

<div class="card mb-3"><div class="card-header">Service Details</div><div class="card-body">
<div class="row">
    <div class="col-md-3 form-group"><label>Employee Code</label><input name="employee_code" class="form-control" value="{{ old('employee_code', $employee->employee_code) }}"></div>
    <div class="col-md-3 form-group"><label>College / Directorate</label><select id="college_id" name="college_id" class="form-control"><option value="">Select</option>@foreach($colleges as $c)<option value="{{ $c->id }}" {{ old('college_id', $employee->college_id)==$c->id?'selected':'' }}>{{ $c->name }}</option>@endforeach</select></div>
    <div class="col-md-3 form-group"><label>Department / Office / KVK</label><select id="department_id" name="department_id" class="form-control"><option value="">Select</option>@foreach($departments as $d)<option value="{{ $d->id }}" data-college="{{ $d->college_id }}" {{ old('department_id', $employee->department_id)==$d->id?'selected':'' }}>{{ $d->name }}{{ $d->place ? ' - '.$d->place : '' }}</option>@endforeach</select></div>
    <div class="col-md-3 form-group"><label>Internal Section</label><select name="section_id" class="form-control"><option value="">Select</option>@foreach($sections as $s)<option value="{{ $s->id }}" {{ old('section_id', $employee->section_id)==$s->id?'selected':'' }}>{{ $s->name }}</option>@endforeach</select></div>
    <div class="col-md-3 form-group"><label>Legacy Directorate</label><select name="directorate_id" class="form-control"><option value="">Select</option>@foreach($directorates as $d)<option value="{{ $d->id }}" {{ old('directorate_id', $employee->directorate_id)==$d->id?'selected':'' }}>{{ $d->name }}</option>@endforeach</select></div>
    <div class="col-md-3 form-group"><label>Room No</label><input name="room_no" class="form-control" value="{{ old('room_no', $employee->room_no) }}"></div>
    <div class="col-md-4 form-group"><label>Designation Master</label><select name="designation_id" class="form-control"><option value="">Select</option>@foreach($designations as $d)<option value="{{ $d->id }}" {{ old('designation_id', $employee->designation_id)==$d->id?'selected':'' }}>{{ $d->name }} {{ $d->cadre ? '('.$d->cadre.')':'' }}</option>@endforeach</select></div>
    <div class="col-md-4 form-group"><label>Manual Designation</label><input name="manual_designation" class="form-control" value="{{ old('manual_designation', $employee->manual_designation) }}" placeholder="Use only if not in master"></div>
    <div class="col-md-4 form-group"><label class="required">Job Type</label><select name="job_type" class="form-control" required>@foreach(['Permanent','Adhoc','Temporary','Daily Wages'] as $j)<option value="{{ $j }}" {{ old('job_type', $employee->job_type)==$j?'selected':'' }}>{{ $j }}</option>@endforeach</select></div>
    <div class="col-md-3 form-group"><label>DOB</label><input type="date" id="dob" name="date_of_birth" class="form-control" value="{{ old('date_of_birth', optional($employee->date_of_birth)->format('Y-m-d')) }}"></div>
    <div class="col-md-3 form-group"><label>DOJ</label><input type="date" id="doj" name="date_of_joining" class="form-control" value="{{ old('date_of_joining', optional($employee->date_of_joining)->format('Y-m-d')) }}"></div>
    <div class="col-md-2 form-group"><label>Retirement Age</label><input type="number" id="retirement_age" name="retirement_age" class="form-control" value="{{ old('retirement_age', $employee->retirement_age ?: 60) }}"></div>
    <div class="col-md-4 form-group"><label>Manual Retirement Date</label><input type="date" name="manual_retirement_date" class="form-control" value="{{ old('manual_retirement_date', optional($employee->manual_retirement_date)->format('Y-m-d')) }}"><small>Auto date is calculated, but this manual date will override.</small></div>
    <div class="col-md-4 form-group"><label>Manual Annual Increment Date</label><input type="date" name="manual_increment_date" class="form-control" value="{{ old('manual_increment_date', optional($employee->manual_increment_date)->format('Y-m-d')) }}"></div>
    <div class="col-md-4 form-group"><label>Increment Remarks</label><input name="increment_remarks" class="form-control" value="{{ old('increment_remarks', $employee->increment_remarks) }}"></div>
    <div class="col-md-4 form-group"><label>Status</label><select name="status" class="form-control">@foreach(['Active','Retired','Transferred','Inactive'] as $st)<option value="{{ $st }}" {{ old('status', $employee->status)==$st?'selected':'' }}>{{ $st }}</option>@endforeach</select></div>
</div>
</div></div>

<div class="card mb-3"><div class="card-header">Identity & Salary Details</div><div class="card-body"><div class="row">
    <div class="col-md-3 form-group"><label>GPF No.</label><input name="gpf_no" class="form-control" value="{{ old('gpf_no', $employee->gpf_no) }}"></div>
    <div class="col-md-3 form-group"><label>NPS No.</label><input name="nps_no" class="form-control" value="{{ old('nps_no', $employee->nps_no) }}"></div>
    <div class="col-md-3 form-group"><label>PAN No.</label><input name="pan_no" class="form-control" value="{{ old('pan_no', $employee->pan_no) }}"></div>
    <div class="col-md-3 form-group"><label>Aadhaar No.</label><input name="aadhaar_no" class="form-control" value="{{ old('aadhaar_no', $employee->aadhaar_no) }}"></div>
    <div class="col-md-4 form-group"><label>Salary Account No.</label><input name="salary_account_no" class="form-control" value="{{ old('salary_account_no', $employee->salary_account_no) }}"></div>
</div></div></div>

<div class="card mb-3"><div class="card-header">Address</div><div class="card-body"><div class="row">
    <div class="col-md-6 form-group"><label>Address Line 1</label><input name="address_line_1" class="form-control" value="{{ old('address_line_1', $employee->address_line_1) }}"></div>
    <div class="col-md-6 form-group"><label>Address Line 2</label><input name="address_line_2" class="form-control" value="{{ old('address_line_2', $employee->address_line_2) }}"></div>
    <div class="col-md-3 form-group"><label>PIN/ZIP</label><input id="zip" name="zip" class="form-control" value="{{ old('zip', $employee->zip) }}"><small id="pin-msg"></small></div>
    <div class="col-md-3 form-group"><label>City</label><input id="manual_city" name="manual_city" class="form-control" value="{{ old('manual_city', $employee->manual_city) }}"></div>
    <div class="col-md-3 form-group"><label>State</label><input id="manual_state" name="manual_state" class="form-control" value="{{ old('manual_state', $employee->manual_state) }}"></div>
    <div class="col-md-3 form-group"><label>Country</label><input id="manual_country" name="manual_country" class="form-control" value="{{ old('manual_country', $employee->manual_country ?: 'India') }}"></div>
    <div class="col-md-12 form-group"><label>Remarks</label><textarea name="remarks" class="form-control">{{ old('remarks', $employee->remarks) }}</textarea></div>
</div></div></div>

@push('scripts')
<script>

function filterDepartments(){
    var collegeId = $('#college_id').val();
    $('#department_id option').each(function(){
        var optCollege = $(this).data('college');
        if(!$(this).val() || !collegeId || optCollege == collegeId){ $(this).show(); } else { $(this).hide(); }
    });
    var selected = $('#department_id option:selected');
    if(collegeId && selected.val() && selected.data('college') != collegeId){
        $('#department_id').val('');
    }
}
$('#college_id').on('change', filterDepartments);
filterDepartments();

$('#zip').on('blur', function(){
    var pin = $(this).val();
    if(pin.length === 6){
        $('#pin-msg').text('Checking PIN...');
        $.get('{{ url('/pincode') }}/' + pin, function(res){
            if(res.success){
                $('#manual_city').val(res.city);
                $('#manual_state').val(res.state);
                $('#manual_country').val(res.country);
                $('#pin-msg').text('Auto filled from PIN.').css('color','green');
            } else {
                $('#pin-msg').text(res.message).css('color','red');
            }
        });
    }
});
</script>
@endpush
