@extends('layouts.app')
@section('content')
@php
    $canPrintProforma = ($request->selectedEstimate && $request->programmer_estimate_status == 'Estimate OK');
@endphp
<div class="d-flex justify-content-between mb-3">
    <h4>Request {{ $request->request_no }}</h4>
    <div>
        @if($canPrintProforma)
            <a class="btn btn-outline-primary" target="_blank" href="{{ route('repair-requests.proforma', $request) }}">Print Financial Sanction Proforma</a>
        @else
            <button type="button" class="btn btn-outline-secondary" disabled title="Print will be enabled after Programmer / Store Incharge verifies the estimate as OK.">Print Proforma Pending Verification</button>
        @endif
        <a class="btn btn-secondary" href="{{ route('repair-requests.index') }}">Back</a>
    </div>
</div>

<div class="row">
<div class="col-md-8">
<div class="card mb-3"><div class="card-header">Request Details</div><div class="card-body table-responsive">
<table class="table table-bordered table-sm">
<tr><th>Employee</th><td>{{ optional($request->employee)->display_name }}</td><th>Status</th><td><span class="badge badge-info">{{ $request->status }}</span></td></tr>
<tr><th>Category</th><td>{{ optional($request->category)->name }} @if(optional($request->category)->item_group)<small>({{ optional($request->category)->item_group }})</small>@endif</td><th>Current Handler</th><td>{{ ucwords(str_replace('_',' ', $request->current_handler_role)) }}</td></tr>
<tr><th>Assigned To</th><td>{{ optional($request->assignedTo)->display_name }}</td><th>Priority</th><td>{{ $request->priority }}</td></tr>
<tr><th>Item</th><td>{{ $request->item_type }} - {{ $request->item_name }}</td><th>Inventory</th><td>{{ $request->inventory_no }}</td></tr>
<tr><th>Room</th><td>{{ $request->room_no }}</td><th>Asset</th><td>{{ optional($request->asset)->item_name }}</td></tr>
<tr><th>Default Problem</th><td colspan="3">{{ optional($request->problemTemplate)->title ?: '-' }}</td></tr>
<tr><th>Problem</th><td colspan="3">{{ $request->problem_description }}</td></tr>
@if($request->attachment_url)<tr><th>Attachment</th><td colspan="3"><a href="{{ $request->attachment_url }}" target="_blank">Open Attachment</a></td></tr>@endif
@if($request->storekeeper_remarks)<tr><th>Storekeeper Remarks</th><td colspan="3">{{ $request->storekeeper_remarks }}</td></tr>@endif
@if($request->programmer_remarks)
<tr><th>Verification / Work Done</th><td colspan="3">{{ $request->programmer_work_done }}</td></tr>
<tr><th>Programmer / Store Incharge Verification</th><td colspan="3"><strong>{{ $request->programmer_estimate_status }}</strong><br>{{ $request->programmer_remarks }}</td></tr>
@endif
@if($request->d4_remarks)<tr><th>D-4 Remarks</th><td colspan="3">{{ $request->d4_remarks }}</td></tr>@endif
</table>
</div></div>

<div class="card mb-3"><div class="card-header bg-light"><strong>Vendor Estimates / Quotations</strong></div><div class="card-body table-responsive">
<table class="table table-bordered table-sm">
<thead><tr><th>Selected</th><th>Vendor</th><th>Amount</th><th>Date</th><th>Details</th><th>Verification Status</th><th>File</th></tr></thead>
<tbody>
@forelse($request->estimates as $estimate)
<tr>
    <td>{!! $estimate->is_selected ? '<span class="badge badge-success">Yes</span>' : '' !!}</td>
    <td>{{ optional($estimate->vendor)->name }}</td>
    <td>Rs. {{ number_format($estimate->estimate_amount,2) }}</td>
    <td>{{ optional($estimate->estimate_date)->format('d-m-Y') }}</td>
    <td>{{ $estimate->estimate_details }}</td>
    <td>{{ $estimate->programmer_verification_status }}<br><small>{{ $estimate->programmer_remarks }}</small></td>
    <td>@if($estimate->estimate_file_url)<a target="_blank" href="{{ $estimate->estimate_file_url }}">Open</a>@else - @endif</td>
</tr>
@empty
<tr><td colspan="7">No vendor estimate entered yet.</td></tr>
@endforelse
</tbody>
</table>
</div></div>

@if(Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper']))
<div class="card mb-3"><div class="card-header bg-light"><strong>Storekeeper Action</strong></div><div class="card-body">
<form method="POST" action="{{ route('repair-requests.storekeeper-action', $request) }}" enctype="multipart/form-data">
@csrf
<div class="row">
    <div class="col-md-4 form-group">
        <label class="required">Action</label>
        <select name="action" class="form-control" required>
            <option value="save_estimate">Save Vendor Estimate</option>
            <option value="forward_to_programmer">Forward Estimate to Programmer</option>
            <option value="forward_to_store_incharge">Forward Estimate to Store Incharge</option>
            <option value="generate_proforma">Generate Proforma after Verification OK</option>
            <option value="mark_submitted_d4">Mark Printed File Submitted to D-4</option>
            <option value="sanction_received">Record Manual Sanction Received</option>
            <option value="sanction_rejected">Record Manual Sanction Rejected</option>
            <option value="work_completed">Mark Work Completed</option>
            <option value="close">Close</option>
            <option value="reject">Reject</option>
        </select>
    </div>
    <div class="col-md-4 form-group">
        <label>Select Existing Estimate</label>
        <select name="selected_estimate_id" class="form-control">
            <option value="">No Change</option>
            @foreach($request->estimates as $estimate)
                <option value="{{ $estimate->id }}" {{ $request->selected_estimate_id == $estimate->id ? 'selected' : '' }}>{{ optional($estimate->vendor)->name }} - Rs. {{ number_format($estimate->estimate_amount,2) }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 form-group">
        <label>Programmer Assign To</label>
        <select name="assigned_to_employee_id" class="form-control">
            <option value="">Auto Programmer</option>
            @foreach(($programmers ?? $employees) as $e)<option value="{{ $e->id }}">{{ $e->display_name }} - {{ $e->designation_name }}</option>@endforeach
        </select>
    </div>
    <div class="col-md-4 form-group">
        <label>Store Incharge Assign To</label>
        <select name="store_incharge_employee_id" class="form-control">
            <option value="">Auto Store Incharge</option>
            @foreach(($storeIncharges ?? collect()) as $e)<option value="{{ $e->id }}">{{ $e->display_name }} - {{ $e->designation_name }}</option>@endforeach
        </select>
        <small class="text-muted">Used only when action is Forward Estimate to Store Incharge.</small>
    </div>

    <div class="col-md-12"><hr><strong>Vendor Allocation / Estimate for this Request</strong><br><small class="text-muted">Storekeeper selects vendor, enters estimated amount, uploads estimate PDF, then forwards the request to Programmer or Store Incharge for department-wise verification remarks.</small></div>
    <div class="col-md-4 form-group"><label>Vendor</label><select name="vendor_id" class="form-control"><option value="">Select Vendor</option>@foreach($vendors as $v)<option value="{{ $v->id }}">{{ $v->name }} ({{ $v->vendor_type }})</option>@endforeach</select></div>
    <div class="col-md-4 form-group"><label>Estimate Amount</label><input type="number" step="0.01" name="estimate_amount" class="form-control"></div>
    <div class="col-md-4 form-group"><label>Estimate Date</label><input type="date" name="estimate_date" value="{{ date('Y-m-d') }}" class="form-control"></div>
    <div class="col-md-8 form-group"><label>Estimate Details</label><textarea name="estimate_details" class="form-control" rows="2" placeholder="Part/material/service details"></textarea></div>
    <div class="col-md-4 form-group"><label>Upload Estimate / Quotation PDF</label><input type="file" name="estimate_file" accept="application/pdf" class="form-control-file"><small class="text-muted">PDF is mandatory when saving a new vendor estimate.</small></div>

    <div class="col-md-12">
        <hr>
        <div class="alert alert-info mb-2">
            After Programmer / Store Incharge marks the estimate OK, the Financial Sanction Proforma will be generated automatically from the selected vendor estimate and request details. No extra proforma fields are required from Storekeeper.
        </div>
    </div>

    <div class="col-md-12 form-group">
        <label class="required">Storekeeper Remarks</label>
        <textarea name="storekeeper_remarks" class="form-control" rows="2" required>{{ old('storekeeper_remarks', $request->storekeeper_remarks) }}</textarea>
    </div>
</div>
<button class="btn btn-success">Submit Storekeeper Action</button>
</form>
</div></div>
@endif

@php $isStoreInchargeUser = Auth::user()->employee && Auth::user()->employee->hasActiveCharge('Store Incharge'); @endphp
@if(Auth::user()->isRole(['admin','college_admin','department_admin','director','programmer']) || $isStoreInchargeUser)
<div class="card mb-3"><div class="card-header bg-light"><strong>Programmer / Store Incharge Verification Remarks</strong></div><div class="card-body">
<form method="POST" action="{{ route('repair-requests.programmer-action', $request) }}">
@csrf
<div class="alert alert-warning mb-3">Programmer or Store Incharge should physically verify the item/repair/installation and confirm whether the vendor estimate is OK.</div>
<div class="row">
    <div class="col-md-4 form-group">
        <label class="required">Action</label>
        <select name="action" class="form-control" required>
            <option value="receive">Receive for Verification</option>
            <option value="estimate_ok">Estimate OK</option>
            <option value="estimate_not_ok">Estimate Not OK</option>
            <option value="need_revised_estimate">Need Revised Estimate</option>
        </select>
    </div>
    <div class="col-md-8 form-group">
        <label>Selected Estimate</label>
        <input class="form-control" readonly value="@if($request->selectedEstimate){{ optional($request->selectedEstimate->vendor)->name }} - Rs. {{ number_format($request->selectedEstimate->estimate_amount,2) }}@else No estimate selected @endif">
    </div>
    <div class="col-md-12 form-group"><label>Physical Verification / Work Done / Observation</label><textarea name="programmer_work_done" class="form-control" rows="3">{{ old('programmer_work_done', $request->programmer_work_done) }}</textarea></div>
    <div class="col-md-12 form-group"><label class="required">Verification Remarks</label><textarea name="programmer_remarks" class="form-control" rows="3" required>{{ old('programmer_remarks', $request->programmer_remarks) }}</textarea></div>
</div>
<button class="btn btn-primary">Submit Verification Remarks</button>
</form>
</div></div>
@endif

@if(Auth::user()->isRole(['admin','college_admin','department_admin','director','d4_seat']))
<div class="card mb-3"><div class="card-header bg-light"><strong>D-4 Seat Manual File Record</strong></div><div class="card-body">
<form method="POST" action="{{ route('repair-requests.d4-action', $request) }}">
@csrf
<div class="row">
    <div class="col-md-4 form-group"><label>Action</label><select name="action" class="form-control"><option value="mark_received">Mark Manual File Received</option><option value="add_note">Add Note Only</option></select></div>
    <div class="col-md-8 form-group"><label class="required">D-4 Remarks</label><input name="d4_remarks" value="{{ $request->d4_remarks }}" class="form-control" required></div>
</div>
<button class="btn btn-dark">Submit D-4 Action</button>
</form>
</div></div>
@endif

@if(Auth::user()->isRole(['admin','college_admin','department_admin','director']))
<div class="card mb-3"><div class="card-header">Admin / Director Manual Correction</div><div class="card-body">
<form method="POST" action="{{ route('repair-requests.status', $request) }}">
@csrf
<div class="row">
    <div class="col-md-3 form-group"><label>Status</label><input name="status" class="form-control" value="{{ $request->status }}"></div>
    <div class="col-md-4 form-group"><label>Assign To</label><select name="assigned_to_employee_id" class="form-control"><option value="">No Change</option>@foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->display_name }} - {{ $e->designation_name }}</option>@endforeach</select></div>
    <div class="col-md-5 form-group"><label>Remarks</label><input name="remarks" class="form-control"></div>
</div>
<button class="btn btn-warning">Manual Update</button>
</form>
</div></div>
@endif

@if(\App\Support\AccessScope::isEmployeeOnly(Auth::user()) || Auth::user()->isRole(['admin','college_admin','department_admin','director']))
<div class="card mb-3"><div class="card-header">Employee Confirmation / Reopen</div><div class="card-body">
<form method="POST" action="{{ route('repair-requests.feedback', $request) }}">
@csrf
<textarea name="employee_feedback" class="form-control mb-2" placeholder="Feedback" required>{{ $request->employee_feedback }}</textarea>
<button name="action" value="confirm" class="btn btn-success">Confirm & Close</button>
<button name="action" value="reopen" class="btn btn-warning">Reopen</button>
</form>
</div></div>
@endif
</div>

<div class="col-md-4">
<div class="card mb-3"><div class="card-header">Financial Sanction Summary</div><div class="card-body">
    <p><strong>Vendor:</strong> {{ $request->selected_vendor_name ?: '-' }}</p>
    <p><strong>Estimate:</strong> {{ $request->proforma_amount ? 'Rs. '.number_format($request->proforma_amount,2) : '-' }}</p>
    <p><strong>Verification Status:</strong> {{ $request->programmer_estimate_status }}</p>
    <p><strong>Manual Sanction:</strong> {{ $request->manual_sanction_status }}</p>
    <p><strong>D-4 Submitted:</strong> {{ optional($request->d4_submitted_at)->format('d-m-Y h:i A') ?: '-' }}</p>
    <p><strong>D-4 Received:</strong> {{ optional($request->d4_received_at)->format('d-m-Y h:i A') ?: '-' }}</p>
    @if($canPrintProforma)
        <a class="btn btn-outline-primary btn-block" target="_blank" href="{{ route('repair-requests.proforma', $request) }}">Print Proforma</a>
    @else
        <button type="button" class="btn btn-outline-secondary btn-block" disabled>Print enabled after verification OK</button>
    @endif
</div></div>
<div class="card"><div class="card-header">History / Track</div><div class="card-body timeline-line">
@forelse($request->logs as $log)
<div class="border-bottom mb-2 pb-2"><strong>{{ $log->action }}</strong><br><small>{{ $log->old_status }} → {{ $log->new_status }}<br>{{ optional($log->user)->name }} | {{ optional($log->created_at)->format('d-m-Y h:i A') }}</small><div>{{ $log->remarks }}</div></div>
@empty
<p>No history found.</p>
@endforelse
</div></div>
</div>
</div>
@endsection
