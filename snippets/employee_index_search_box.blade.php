{{-- Add this in resources/views/employees/index.blade.php inside the filter form --}}
<div class="form-group col-md-3">
    <label>Search Name / Phone</label>
    <input type="text" name="q" value="{{ request('q') }}" class="form-control" placeholder="Name, phone, employee code">
</div>

{{-- Use this for clean name display to avoid Dr. Dr. --}}
{{ \App\Support\EmployeeFormatter::displayName($employee) }}
