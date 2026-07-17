@php
    $selectedRoles = old('roles', isset($employee) && $employee->user && method_exists($employee->user, 'roleNames') ? $employee->user->roleNames() : ['employee']);
@endphp
<div class="form-group">
    <label>System Roles</label>
    <select name="roles[]" class="form-control" multiple>
        @foreach($assignableRoles as $role)
            <option value="{{ $role->slug }}" {{ in_array($role->slug, $selectedRoles) ? 'selected' : '' }}>
                {{ $role->display_name ?: $role->name }}
            </option>
        @endforeach
    </select>
    <small class="text-muted">
        Superuser can assign all roles. College/Admin/Director and Department Admin can assign only allowed roles within their scope.
    </small>
</div>
