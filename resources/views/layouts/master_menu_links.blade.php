@php
    $u = auth()->user();
    $canSeeMasters = $u && (method_exists($u, 'hasAnyRole') ? $u->hasAnyRole(['superuser']) : ($u->role === 'superuser'));
@endphp

@if($canSeeMasters)
    <div class="dropdown-divider"></div>
    <h6 class="dropdown-header">Master Setup</h6>
    <a class="dropdown-item" href="{{ route('master.colleges.index') }}">Colleges / Directorates</a>
    <a class="dropdown-item" href="{{ route('master.departments.index') }}">Departments / Offices / KVKs</a>
    <a class="dropdown-item" href="{{ route('master.designations.index') }}">Designations</a>
@endif
