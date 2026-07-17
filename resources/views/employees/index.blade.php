@extends('layouts.app')
@section('content')
    <div class="d-flex justify-content-between mb-3">
        <h4>Employees</h4>
        <a href="{{ route('employees.create') }}" class="btn btn-success">Add Employee</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" action="{{ route('employees.index') }}" class="row">
                <div class="col-md-3 form-group">
                    <label>Search Employee</label>
                    <input type="text" name="search" value="{{ $search ?? request('search') }}" class="form-control"
                        placeholder="Name, phone, code, GPF/NPS, PAN">
                </div>

                <div class="col-md-3 form-group">
                    <label>College / Directorate</label>
                    <select name="college_id" class="form-control">
                        <option value="">All</option>
                        @foreach ($colleges as $c)
                            <option value="{{ $c->id }}" {{ request('college_id') == $c->id ? 'selected' : '' }}>
                                {{ $c->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-3 form-group">
                    <label>Department / Office / KVK</label>
                    <select name="department_id" class="form-control">
                        <option value="">All</option>
                        @foreach ($departments as $d)
                            <option value="{{ $d->id }}" {{ request('department_id') == $d->id ? 'selected' : '' }}>
                                {{ $d->name }}{{ $d->place ? ' - ' . $d->place : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 form-group">
                    <label>Status</label>
                    <select name="status" class="form-control">
                        <option value="">All</option>
                        @foreach (['Active', 'Retired', 'Transferred', 'Inactive'] as $st)
                            <option value="{{ $st }}" {{ request('status') == $st ? 'selected' : '' }}>
                                {{ $st }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-1 form-group pt-4">
                    <button type="submit" class="btn btn-primary btn-block">Search</button>
                </div>

                <div class="col-md-12 mt-2">
                    <a href="{{ route('employees.index') }}" class="btn btn-secondary btn-sm">Reset</a>

                    @if (request('search'))
                        <span class="ml-2 text-muted">
                            Search: <strong>{{ request('search') }}</strong>
                        </span>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body table-responsive">
            <table class="table table-bordered table-sm">
                <thead>
                    <tr>
                        <th>Photo</th>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Designation</th>
                        <th>College/Directorate</th>
                        <th>Department</th>
                        <th>Section</th>
                        <th>Charges</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($employees as $employee)
                        <tr>
                            <td>
                                @if ($employee->photo_url)
                                    <img src="{{ $employee->photo_url }}" class="photo-thumb">
                                @endif
                            </td>
                            <td>{{ $employee->display_name }}</td>
                            <td>{{ $employee->phone }}</td>
                            <td>{{ $employee->designation_name }}</td>
                            <td>{{ optional($employee->college)->name }}</td>
                            <td>{{ optional($employee->department)->name }}</td>
                            <td>{{ optional($employee->section)->name }}</td>
                            <td>
                                @foreach ($employee->activeCharges as $c)
                                    <span class="badge badge-secondary">{{ $c->charge_name }}</span>
                                @endforeach
                            </td>
                            <td>{{ $employee->status }}</td>
                            <td>
                                <a href="{{ route('employees.show', $employee) }}" class="btn btn-sm btn-info">View</a>
                                <a href="{{ route('employees.edit', $employee) }}" class="btn btn-sm btn-primary">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="10" class="text-center text-muted">No employee found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
            {{ $employees->appends(request()->query())->links() }}
        </div>
    </div>
@endsection
