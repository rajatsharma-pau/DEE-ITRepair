@extends('layouts.app')

@section('content')
@php
    $loggedUser = Auth::user();

    $canCreateRequest =
        \App\Support\AccessScope::isEmployeeOnly($loggedUser)
        || (
            $loggedUser
            && method_exists($loggedUser, 'isRole')
            && $loggedUser->isRole([
                'admin',
                'college_admin',
                'department_admin',
                'director',
                'storekeeper'
            ])
        );

    $activeHandler = request('handler');
@endphp

<div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-1">Repair / Material Requests</h4>

        @if($activeHandler)
            <small class="text-muted">
                Queue:
                <strong>{{ ucwords(str_replace('_', ' ', $activeHandler)) }}</strong>
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
    Employee → Storekeeper → Vendor Estimate → Programmer / Store Incharge Verification
    → Storekeeper prints Financial Sanction → Manual submission to D-4.
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
                <div class="col-md-4 mb-2">
                    <label class="small font-weight-bold">
                        Search
                    </label>

                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           class="form-control"
                           placeholder="Request no., employee, phone, category, problem, vendor, status, handler or assigned person">
                </div>

                @if(
                    $loggedUser
                    && method_exists($loggedUser, 'isRole')
                    && $loggedUser->isRole([
                        'admin',
                        'college_admin',
                        'department_admin',
                        'director',
                        'storekeeper'
                    ])
                )
                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold">
                            College / Directorate
                        </label>

                        <select name="college_id"
                                class="form-control">

                            <option value="">
                                All Colleges / Directorates
                            </option>

                            @foreach($colleges ?? [] as $c)
                                <option value="{{ $c->id }}"
                                    {{ (string) request('college_id') === (string) $c->id ? 'selected' : '' }}>
                                    {{ $c->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-2 mb-2">
                        <label class="small font-weight-bold">
                            Department
                        </label>

                        <select name="department_id"
                                class="form-control">

                            <option value="">
                                All Departments
                            </option>

                            @foreach($departments ?? [] as $d)
                                <option value="{{ $d->id }}"
                                    {{ (string) request('department_id') === (string) $d->id ? 'selected' : '' }}>
                                    {{ $d->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif

                <div class="col-md-2 mb-2">
                    <label class="small font-weight-bold">
                        Status
                    </label>

                    <input type="text"
                           name="status"
                           value="{{ request('status') }}"
                           class="form-control"
                           placeholder="Status">
                </div>

                <div class="col-md-2 mb-2 d-flex align-items-end">
                    <button type="submit"
                            class="btn btn-primary btn-block mr-1">
                        Search
                    </button>

                    <a href="{{ route(
                            'repair-requests.index',
                            $activeHandler ? ['handler' => $activeHandler] : []
                        ) }}"
                       class="btn btn-secondary">
                        Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm">
    <div class="card-body table-responsive">

        <div class="d-flex justify-content-between align-items-center mb-2">
            <div class="text-muted">
                Total Records: <strong>{{ $requests->total() }}</strong>
            </div>

            @if(request('search'))
                <div class="small">
                    Search:
                    <strong>{{ request('search') }}</strong>
                </div>
            @endif
        </div>

        <table class="table table-bordered table-sm table-hover">
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

                        <td title="{{ $r->problem_description }}">
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

                        <td>
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
                            No repair requests found for the selected search or filters.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{ $requests->appends(request()->query())->links() }}
    </div>
</div>
@endsection