@extends('layouts.app')

@section('content')
@php
    $loggedUser = Auth::user();

    $hasAnyRole = function ($roles) use ($loggedUser) {
        if (!$loggedUser) {
            return false;
        }

        $roles = is_array($roles) ? $roles : [$roles];

        if (
            method_exists($loggedUser, 'hasAnyRole')
            && $loggedUser->hasAnyRole($roles)
        ) {
            return true;
        }

        foreach ($roles as $role) {
            if (
                method_exists($loggedUser, 'hasRole')
                && $loggedUser->hasRole($role)
            ) {
                return true;
            }

            if (
                method_exists($loggedUser, 'isRole')
                && $loggedUser->isRole([$role])
            ) {
                return true;
            }
        }

        return false;
    };

    $isSuperuser = $hasAnyRole(['superuser']);
    $isStorekeeper = $hasAnyRole(['storekeeper']);
    $isProgrammer = $hasAnyRole(['programmer']);
    $isStoreIncharge = $hasAnyRole(['store_incharge']);
    $isD4Seat = $hasAnyRole(['d4_seat']);

    $isAdminLevel = $hasAnyRole([
        'admin',
        'college_admin',
        'department_admin',
        'director',
    ]);

    $canCreateRequest =
        \App\Support\AccessScope::isEmployeeOnly($loggedUser)
        || $hasAnyRole([
            'admin',
            'college_admin',
            'department_admin',
            'director',
            'storekeeper',
        ]);

    /*
     * Storekeeper scope is fixed to the Storekeeper's assigned
     * College/Directorate and Department.
     */
    $lockStorekeeperScope =
        $isStorekeeper
        && !$isSuperuser;

    $fixedCollegeId = isset($fixedCollegeId)
        ? $fixedCollegeId
        : \App\Support\AccessScope::collegeId($loggedUser);

    $fixedDepartmentId = isset($fixedDepartmentId)
        ? $fixedDepartmentId
        : \App\Support\AccessScope::departmentId($loggedUser);

    $selectedCollegeId = request(
        'college_id',
        $lockStorekeeperScope ? $fixedCollegeId : null
    );

    $selectedDepartmentId = request(
        'department_id',
        $lockStorekeeperScope ? $fixedDepartmentId : null
    );

    $activeHandler = request('handler');

    /*
     * Role-aware Status dropdown.
     */
    if ($isSuperuser) {
        $statusOptions = [
            'Submitted to Storekeeper',
            'Estimate Taken by Storekeeper',
            'Sent to Programmer for Verification',
            'Sent to Store Incharge for Verification',
            'Programmer Received for Verification',
            'Store Incharge Received for Verification',
            'Programmer Verified Estimate OK',
            'Store Incharge Verified Estimate OK',
            'Programmer Returned - Estimate Not OK',
            'Store Incharge Returned - Estimate Not OK',
            'Programmer Asked for Revised Estimate',
            'Store Incharge Asked for Revised Estimate',
            'Financial Sanction Proforma Ready',
            'Submitted Manually to D-4',
            'D-4 Received Manual File',
            'D-4 Put Up for Sanction',
            'Sanction Received',
            'Sanction Rejected',
            'Work Completed - Employee Confirmation Pending',
            'Reopened',
            'Closed',
            'Rejected',
        ];
    } elseif ($isStorekeeper) {
        $statusOptions = [
            'Submitted to Storekeeper',
            'Estimate Taken by Storekeeper',
            'Sent to Programmer for Verification',
            'Sent to Store Incharge for Verification',
            'Programmer Verified Estimate OK',
            'Store Incharge Verified Estimate OK',
            'Programmer Returned - Estimate Not OK',
            'Store Incharge Returned - Estimate Not OK',
            'Programmer Asked for Revised Estimate',
            'Store Incharge Asked for Revised Estimate',
            'Financial Sanction Proforma Ready',
            'Submitted Manually to D-4',
            'D-4 Put Up for Sanction',
            'Sanction Received',
            'Sanction Rejected',
            'Work Completed - Employee Confirmation Pending',
            'Closed',
            'Rejected',
        ];
    } elseif ($isProgrammer || $isStoreIncharge) {
        $statusOptions = [
            'Sent to Programmer for Verification',
            'Sent to Store Incharge for Verification',
            'Programmer Received for Verification',
            'Store Incharge Received for Verification',
            'Programmer Verified Estimate OK',
            'Store Incharge Verified Estimate OK',
            'Programmer Returned - Estimate Not OK',
            'Store Incharge Returned - Estimate Not OK',
            'Programmer Asked for Revised Estimate',
            'Store Incharge Asked for Revised Estimate',
        ];
    } elseif ($isD4Seat) {
        $statusOptions = [
            'Submitted Manually to D-4',
            'D-4 Received Manual File',
            'D-4 Put Up for Sanction',
            'Sanction Received',
            'Sanction Rejected',
        ];
    } elseif ($isAdminLevel) {
        $statusOptions = [
            'Submitted to Storekeeper',
            'Estimate Taken by Storekeeper',
            'Sent to Programmer for Verification',
            'Sent to Store Incharge for Verification',
            'Programmer Verified Estimate OK',
            'Store Incharge Verified Estimate OK',
            'Financial Sanction Proforma Ready',
            'Submitted Manually to D-4',
            'D-4 Put Up for Sanction',
            'Sanction Received',
            'Sanction Rejected',
            'Work Completed - Employee Confirmation Pending',
            'Closed',
            'Rejected',
        ];
    } else {
        $statusOptions = [
            'Submitted to Storekeeper',
            'Estimate Taken by Storekeeper',
            'Sent to Programmer for Verification',
            'Sent to Store Incharge for Verification',
            'Programmer Verified Estimate OK',
            'Store Incharge Verified Estimate OK',
            'Submitted Manually to D-4',
            'D-4 Put Up for Sanction',
            'Sanction Received',
            'Sanction Rejected',
            'Work Completed - Employee Confirmation Pending',
            'Reopened',
            'Closed',
            'Rejected',
        ];
    }

    $statusOptions = array_values(array_unique($statusOptions));
@endphp

@push('styles')
<style>
    .rr-filter-label {
        font-size: 12px;
        font-weight: 700;
        color: #495057;
        margin-bottom: 4px;
    }

    .rr-fixed-field {
        background: #f8f9fa;
    }

    .rr-table th {
        white-space: nowrap;
        vertical-align: middle;
    }

    .rr-table td {
        vertical-align: middle;
    }

    .rr-problem {
        min-width: 190px;
        max-width: 300px;
    }

    .rr-status {
        min-width: 180px;
    }

    .rr-search-help {
        font-size: 11px;
        color: #6c757d;
    }
</style>
@endpush

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Repair / Material Requests</h4>

        @if($activeHandler)
            <small class="text-muted">
                Queue:
                <strong>
                    {{ ucwords(str_replace('_', ' ', $activeHandler)) }}
                </strong>
            </small>
        @endif
    </div>

    @if($canCreateRequest)
        <a href="{{ route('repair-requests.create') }}"
           class="btn btn-success">
            New Request
        </a>
    @endif
</div>

<div class="alert alert-info">
    <strong>Workflow:</strong>
    Employee → Storekeeper → Vendor Estimate →
    Programmer / Store Incharge Verification →
    Storekeeper prints Financial Sanction →
    Manual submission to D-4.
</div>

<div class="card mb-3">
    <div class="card-body">
        <form method="GET"
              action="{{ route('repair-requests.index') }}">

            @if($activeHandler)
                <input type="hidden"
                       name="handler"
                       value="{{ $activeHandler }}">
            @endif

            <div class="form-row">

                <div class="col-lg-4 col-md-6 mb-2">
                    <label class="rr-filter-label">
                        Search
                    </label>

                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="form-control"
                           placeholder="Request no., employee, phone, category, problem, vendor, handler or assigned person">

                    <small class="rr-search-help">
                        Search by employee name, phone number, request number,
                        problem, vendor, handler or assigned employee.
                    </small>
                </div>

                @if($isSuperuser || $isAdminLevel || $isStorekeeper)

                    <div class="col-lg-2 col-md-3 mb-2">
                        <label class="rr-filter-label">
                            College / Directorate
                        </label>

                        @if($lockStorekeeperScope)
                            <input type="hidden"
                                   name="college_id"
                                   value="{{ $fixedCollegeId }}">

                            <select class="form-control rr-fixed-field"
                                    disabled>
                                @forelse($colleges ?? [] as $c)
                                    @if(
                                        (string) $fixedCollegeId
                                        === (string) $c->id
                                    )
                                        <option selected>
                                            {{ $c->name }}
                                        </option>
                                    @endif
                                @empty
                                    <option selected>
                                        Assigned College / Directorate
                                    </option>
                                @endforelse
                            </select>

                            <small class="rr-search-help">
                                Fixed according to your assigned office.
                            </small>
                        @else
                            <select name="college_id"
                                    class="form-control">

                                <option value="">
                                    All Colleges / Directorates
                                </option>

                                @foreach($colleges ?? [] as $c)
                                    <option value="{{ $c->id }}"
                                        {{ (string) $selectedCollegeId === (string) $c->id ? 'selected' : '' }}>

                                        {{ $c->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                    <div class="col-lg-2 col-md-3 mb-2">
                        <label class="rr-filter-label">
                            Department
                        </label>

                        @if($lockStorekeeperScope)
                            <input type="hidden"
                                   name="department_id"
                                   value="{{ $fixedDepartmentId }}">

                            <select class="form-control rr-fixed-field"
                                    disabled>
                                @forelse($departments ?? [] as $d)
                                    @if(
                                        (string) $fixedDepartmentId
                                        === (string) $d->id
                                    )
                                        <option selected>
                                            {{ $d->name }}
                                        </option>
                                    @endif
                                @empty
                                    <option selected>
                                        Assigned Department
                                    </option>
                                @endforelse
                            </select>

                            <small class="rr-search-help">
                                Fixed according to your assigned department.
                            </small>
                        @else
                            <select name="department_id"
                                    class="form-control">

                                <option value="">
                                    All Departments
                                </option>

                                @foreach($departments ?? [] as $d)
                                    <option value="{{ $d->id }}"
                                        {{ (string) $selectedDepartmentId === (string) $d->id ? 'selected' : '' }}>

                                        {{ $d->name }}
                                    </option>
                                @endforeach
                            </select>
                        @endif
                    </div>

                @endif

                <div class="col-lg-2 col-md-3 mb-2">
                    <label class="rr-filter-label">
                        Status
                    </label>

                    <select name="status"
                            class="form-control">

                        <option value="">
                            All Relevant Statuses
                        </option>

                        @foreach($statusOptions as $statusOption)
                            <option value="{{ $statusOption }}"
                                {{ request('status') === $statusOption ? 'selected' : '' }}>

                                {{ $statusOption }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-2 col-md-3 mb-2 d-flex align-items-end">
                    <div class="w-100 d-flex">
                        <button type="submit"
                                class="btn btn-primary flex-fill mr-1">
                            Search
                        </button>

                        <a href="{{ route(
                                'repair-requests.index',
                                $activeHandler
                                    ? ['handler' => $activeHandler]
                                    : []
                            ) }}"
                           class="btn btn-secondary">
                            Reset
                        </a>
                    </div>
                </div>

            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body table-responsive">

        <div class="d-flex flex-wrap justify-content-between align-items-center mb-2">
            <div class="text-muted">
                Total Records:
                <strong>{{ $requests->total() }}</strong>
            </div>

            @if(request('search'))
                <div class="small">
                    Search:
                    <strong>{{ request('search') }}</strong>
                </div>
            @endif
        </div>

        <table class="table table-bordered table-sm table-hover rr-table">
            <thead class="thead-light">
                <tr>
                    <th>No.</th>
                    <th>Employee</th>
                    <th>Category</th>
                    <th>Problem</th>
                    <th>Vendor / Estimate</th>
                    <th>Status</th>
                    <th>Handler</th>
                    <th>Assigned</th>
                    <th>Action</th>
                </tr>
            </thead>

            <tbody>
                @forelse($requests as $r)
                    <tr>
                        <td>
                            <strong>{{ $r->request_no }}</strong>
                        </td>

                        <td>
                            <strong>
                                {{ optional($r->employee)->display_name ?: '-' }}
                            </strong>

                            @if(optional(optional($r->employee)->user)->phone)
                                <br>
                                <small class="text-muted">
                                    {{ optional(optional($r->employee)->user)->phone }}
                                </small>
                            @elseif(optional($r->employee)->phone)
                                <br>
                                <small class="text-muted">
                                    {{ optional($r->employee)->phone }}
                                </small>
                            @endif
                        </td>

                        <td>
                            {{ optional($r->category)->name ?: '-' }}
                        </td>

                        <td class="rr-problem"
                            title="{{ $r->problem_description }}">

                            {{ \Illuminate\Support\Str::limit(
                                $r->problem_description,
                                70
                            ) }}
                        </td>

                        <td>
                            @if($r->selectedEstimate)
                                <strong>
                                    {{ optional($r->selectedEstimate->vendor)->name ?: '-' }}
                                </strong>

                                <br>

                                <small>
                                    Rs.
                                    {{ number_format(
                                        $r->selectedEstimate->estimate_amount,
                                        2
                                    ) }}
                                </small>
                            @else
                                -
                            @endif
                        </td>

                        <td class="rr-status">
                            <span class="badge badge-info">
                                {{ $r->status ?: 'Pending' }}
                            </span>

                            @if($r->manual_sanction_status)
                                <br>
                                <small class="text-muted">
                                    {{ $r->manual_sanction_status }}
                                </small>
                            @endif
                        </td>

                        <td>
                            {{ ucwords(
                                str_replace(
                                    '_',
                                    ' ',
                                    $r->current_handler_role ?: '-'
                                )
                            ) }}
                        </td>

                        <td>
                            {{ optional($r->assignedTo)->display_name ?: '-' }}
                        </td>

                        <td>
                            <a class="btn btn-sm btn-info"
                               href="{{ route('repair-requests.show', $r) }}">
                                View
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9"
                            class="text-center text-muted py-4">

                            No repair requests found for the selected search
                            or filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $requests->appends(request()->query())->links() }}
    </div>
</div>
@endsection
