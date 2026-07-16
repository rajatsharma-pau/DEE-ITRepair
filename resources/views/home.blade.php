@extends('layouts.app')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <div>
        <h4 class="mb-0">{{ $dashboardTitle ?? 'Dashboard' }}</h4>
        <small class="text-muted">{{ $dashboardSubtitle ?? '' }}</small>
    </div>
    <div>
        <a href="{{ route('repair-requests.create') }}" class="btn btn-sm btn-success">New Repair Request</a>
        <a href="{{ route('store-indents.create') }}" class="btn btn-sm btn-outline-success">New Indent</a>
    </div>
</div>

@if($isEmployeeDashboard ?? false)
    <div class="alert alert-light border mb-3">
        <strong>Employee quick panel:</strong> submit request, see allocated assets, track requests, submit store indent and update profile photo.
    </div>
@else
    <div class="alert alert-light border mb-3">
        <strong>Workflow:</strong> Employee request → Storekeeper vendor estimate/PDF → Programmer or Store Incharge verification → Storekeeper proforma print → Manual D-4 process.
    </div>
@endif

<div class="row">
    @foreach($cards as $card)
        <div class="col-6 col-sm-4 col-md-3 col-xl-2 mb-3">
            <a class="dashboard-card" href="{{ $card['url'] }}">
                <div class="card dash-border-{{ $card['class'] ?? 'primary' }}">
                    <div class="card-body text-center">
                        <div class="dashboard-card-count">{{ $card['count'] }}</div>
                        <div class="dashboard-card-title">{{ $card['title'] }}</div>
                        <div class="dashboard-card-text">{{ $card['text'] ?? 'Open' }}</div>
                    </div>
                </div>
            </a>
        </div>
    @endforeach
</div>

<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <span>{{ ($isEmployeeDashboard ?? false) ? 'My Recent Repair / Material Requests' : 'Recent Repair / Material Requests' }}</span>
        <a href="{{ route('repair-requests.index') }}" class="btn btn-sm btn-outline-primary">View All</a>
    </div>
    <div class="card-body table-responsive">
        <table class="table table-bordered table-sm mb-0">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Employee</th>
                    <th>Category</th>
                    <th>Vendor/Estimate</th>
                    <th>Status</th>
                    <th>Assigned To</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
            @forelse($requests as $r)
                <tr>
                    <td>{{ $r->request_no }}</td>
                    <td>{{ optional($r->employee)->display_name }}</td>
                    <td>{{ optional($r->category)->name }}</td>
                    <td>
                        @if($r->selectedEstimate)
                            {{ optional($r->selectedEstimate->vendor)->name }}<br>
                            <small>Rs. {{ number_format($r->selectedEstimate->estimate_amount,2) }}</small>
                        @else
                            -
                        @endif
                    </td>
                    <td><span class="badge badge-info">{{ $r->status }}</span></td>
                    <td>{{ optional($r->assignedTo)->display_name ?: '-' }}</td>
                    <td><a class="btn btn-sm btn-primary" href="{{ route('repair-requests.show', $r) }}">View</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted">No request found.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
