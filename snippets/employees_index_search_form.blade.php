<form method="GET" action="{{ route('employees.index') }}" class="mb-3">
    <div class="row">
        <div class="col-md-4 mb-2">
            <input type="text"
                   name="search"
                   value="{{ request('search', request('q')) }}"
                   class="form-control"
                   placeholder="Search by name, phone, employee code, GPF/NPS, PAN">
        </div>

        <div class="col-md-3 mb-2">
            <select name="college_id" class="form-control">
                <option value="">All Colleges / Directorates</option>
                @foreach(($colleges ?? []) as $college)
                    <option value="{{ $college->id }}" {{ request('college_id') == $college->id ? 'selected' : '' }}>
                        {{ $college->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-3 mb-2">
            <select name="department_id" class="form-control">
                <option value="">All Departments</option>
                @foreach(($departments ?? []) as $department)
                    <option value="{{ $department->id }}" {{ request('department_id') == $department->id ? 'selected' : '' }}>
                        {{ $department->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-md-2 mb-2">
            <select name="status" class="form-control">
                <option value="">All Status</option>
                <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
                <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                <option value="Retired" {{ request('status') == 'Retired' ? 'selected' : '' }}>Retired</option>
                <option value="Transferred" {{ request('status') == 'Transferred' ? 'selected' : '' }}>Transferred</option>
            </select>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <button type="submit" class="btn btn-primary btn-sm">Search</button>
            <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm">Reset</a>
        </div>
    </div>
</form>
