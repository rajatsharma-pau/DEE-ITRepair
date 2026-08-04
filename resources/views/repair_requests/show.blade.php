@extends('layouts.app')

@section('content')
@php
    $user = Auth::user();
    $loggedUser = $user;
    $userEmployee = $user ? $user->employee : null;

    $hasAnyRole = function ($roles) use ($user) {
        if (!$user) { return false; }

        $roles = is_array($roles) ? $roles : [$roles];

        if (method_exists($user, 'hasAnyRole') && $user->hasAnyRole($roles)) {
            return true;
        }

        foreach ($roles as $role) {
            if (method_exists($user, 'hasRole') && $user->hasRole($role)) { return true; }
            if (method_exists($user, 'isRole') && $user->isRole($role)) { return true; }
        }

        return false;
    };

    $isSuperuser = $hasAnyRole(['superuser']);
    $isStoreInchargeUser = $userEmployee && method_exists($userEmployee, 'hasActiveCharge') && $userEmployee->hasActiveCharge('Store Incharge');

    /*
     * Role-specific workflow panels.
     * Storekeeper should see only storekeeper work.
     * Programmer / Store Incharge should see only verification and return to storekeeper.
     * D-4 should see only D-4 manual file record.
     */
    $workflowAccessClass = '\\App\\Support\\RepairRequestWorkflowAccess';

    if (class_exists($workflowAccessClass)) {
        $canStorekeeperEstimate = method_exists($workflowAccessClass, 'canShowStorekeeperEstimatePanel')
            ? $workflowAccessClass::canShowStorekeeperEstimatePanel($request, $loggedUser)
            : false;

        $canVerifyAndReturn = method_exists($workflowAccessClass, 'canShowVerificationPanel')
            ? $workflowAccessClass::canShowVerificationPanel($request, $loggedUser)
            : false;

        $canSubmitD4 = method_exists($workflowAccessClass, 'canShowD4SubmitPanel')
            ? $workflowAccessClass::canShowD4SubmitPanel($request, $loggedUser)
            : false;

        $verifierLabel = method_exists($workflowAccessClass, 'verifierLabel')
            ? $workflowAccessClass::verifierLabel($request)
            : 'Programmer / Store Incharge';
    } else {
        /* Fallback if support class is not copied yet. */
        $canStorekeeperEstimate = $isSuperuser || $hasAnyRole(['storekeeper']);
        $canVerifyAndReturn = $isSuperuser || $hasAnyRole(['programmer','store_incharge']) || $isStoreInchargeUser;
        $canSubmitD4 = $isSuperuser || $hasAnyRole(['d4_seat']);
        $verifierLabel = 'Programmer / Store Incharge';
    }

    /* Keep old variable names safe for any included snippets. */
    $canStorekeeperAction = $canStorekeeperEstimate;
    $canProgrammerAction = $canVerifyAndReturn;

    /*
     * D-4 must be able to work after the physical file reaches D-4.
     * Do not rely only on the optional workflow support class.
     */
    $isD4User = $isSuperuser || $hasAnyRole(['d4_seat']);

    $isD4WorkflowFile =
        $request->current_handler_role === 'd4_seat'
        || in_array($request->manual_sanction_status, [
            'Submitted to D-4',
            'Received at D-4',
            'Put Up for Sanction',
        ], true)
        || in_array($request->status, [
            'Submitted Manually to D-4',
            'D-4 Received Manual File',
            'D-4 Put Up for Sanction',
        ], true);

    $canD4Action = $isD4User && $isD4WorkflowFile;

    $canD4MarkReceived =
        $canD4Action
        && (
            $request->manual_sanction_status === 'Submitted to D-4'
            || $request->status === 'Submitted Manually to D-4'
        );

    $canD4PutUp =
        $canD4Action
        && (
            $request->manual_sanction_status === 'Received at D-4'
            || $request->manual_sanction_status === 'Put Up for Sanction'
            || $request->status === 'D-4 Received Manual File'
            || $request->status === 'D-4 Put Up for Sanction'
        );

    /*
     * Manual correction should not appear when a role-specific workflow action is pending.
     * This keeps Programmer / Store Incharge screens clean.
     */
    $canManualUpdate = ($isSuperuser || $hasAnyRole(['admin','director']))
        && !$canStorekeeperEstimate
        && !$canVerifyAndReturn
        && !$canSubmitD4;

    $canFeedback = (
            ($userEmployee && $request->employee_id == $userEmployee->id)
            || $isSuperuser
            || $hasAnyRole(['admin','college_admin','department_admin','director'])
        )
        && !$canStorekeeperEstimate
        && !$canVerifyAndReturn
        && !$canSubmitD4;

    $canPrintProforma = ($request->selectedEstimate && $request->programmer_estimate_status == 'Estimate OK');

    $badgeClass = function ($value) {
        $v = strtolower((string) $value);
        if ($v === '' || $v === 'pending') { return 'secondary'; }
        if (strpos($v, 'closed') !== false || strpos($v, 'completed') !== false || strpos($v, 'estimate ok') !== false || strpos($v, 'received') !== false) { return 'success'; }
        if (strpos($v, 'reject') !== false || strpos($v, 'not ok') !== false) { return 'danger'; }
        if (strpos($v, 'revised') !== false || strpos($v, 'submitted') !== false || strpos($v, 'verification') !== false || strpos($v, 'sent') !== false) { return 'warning'; }
        if (strpos($v, 'storekeeper') !== false || strpos($v, 'programmer') !== false || strpos($v, 'd-4') !== false) { return 'info'; }
        return 'primary';
    };

    $priorityClass = function ($value) {
        $v = strtolower((string) $value);
        if (strpos($v, 'urgent') !== false || strpos($v, 'high') !== false) { return 'danger'; }
        if (strpos($v, 'medium') !== false) { return 'warning'; }
        if (strpos($v, 'low') !== false) { return 'secondary'; }
        return 'info';
    };
@endphp

@push('styles')
<style>
    .rr-header-card { border-left:4px solid #1f7f4c; }
    .rr-label { font-size:12px; color:#6c757d; font-weight:700; text-transform:uppercase; letter-spacing:.02em; margin-bottom:2px; }
    .rr-value { font-size:14px; font-weight:600; word-break:break-word; }
    .rr-required:after { content:' *'; color:#dc3545; font-weight:700; }
    .rr-small-help { font-size:12px; color:#6c757d; }
    .rr-action-card .card-header { cursor:pointer; }
    .rr-table th { width:22%; background:#f8f9fa; }
    .rr-timeline { max-height:430px; overflow:auto; }
    .rr-timeline-item { border-left:3px solid #dee2e6; padding-left:10px; margin-left:4px; }
    .rr-sticky-actions { position:sticky; top:10px; z-index:2; }
    @media (max-width: 767.98px) {
        .rr-sticky-actions { position:static; }
    }
</style>
@endpush

<div class="d-flex flex-wrap justify-content-between align-items-start mb-3">
    <div>
        <h4 class="mb-1">Repair Request: {{ $request->request_no }}</h4>
        <div>
            <span class="badge badge-{{ $badgeClass($request->status) }}">{{ $request->status ?: 'Pending' }}</span>
            <span class="badge badge-{{ $priorityClass($request->priority) }}">{{ $request->priority ?: 'Normal' }}</span>
            <span class="text-muted ml-1">Created: {{ optional($request->created_at)->format('d-m-Y h:i A') ?: '-' }}</span>
        </div>
    </div>

    <div class="mt-2 mt-md-0">
        @if($canPrintProforma)
            <a class="btn btn-outline-primary btn-sm" target="_blank" href="{{ route('repair-requests.proforma', $request) }}">Print Financial Sanction Proforma</a>
        @else
            <button type="button" class="btn btn-outline-secondary btn-sm" disabled title="Print will be enabled after Programmer / Store Incharge verifies the estimate as OK.">Print Pending Verification</button>
        @endif
        <a class="btn btn-secondary btn-sm" href="{{ route('repair-requests.index') }}">Back</a>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-3 mb-2">
        <div class="card rr-header-card h-100"><div class="card-body py-3">
            <div class="rr-label">Current Handler</div>
            <div class="rr-value">{{ ucwords(str_replace('_',' ', $request->current_handler_role ?: 'Not assigned')) }}</div>
        </div></div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card rr-header-card h-100"><div class="card-body py-3">
            <div class="rr-label">Assigned To</div>
            <div class="rr-value">{{ optional($request->assignedTo)->display_name ?: '-' }}</div>
        </div></div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card rr-header-card h-100"><div class="card-body py-3">
            <div class="rr-label">Selected Vendor</div>
            <div class="rr-value">{{ $request->selected_vendor_name ?: '-' }}</div>
        </div></div>
    </div>
    <div class="col-md-3 mb-2">
        <div class="card rr-header-card h-100"><div class="card-body py-3">
            <div class="rr-label">Estimate Amount</div>
            <div class="rr-value">{{ $request->proforma_amount ? 'Rs. '.number_format($request->proforma_amount,2) : '-' }}</div>
        </div></div>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card mb-3">
            <div class="card-header bg-white"><strong>Request Details</strong></div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm rr-table mb-0">
                    <tr>
                        <th>Employee</th><td>{{ optional($request->employee)->display_name ?: '-' }}</td>
                        <th>Status</th><td><span class="badge badge-{{ $badgeClass($request->status) }}">{{ $request->status ?: '-' }}</span></td>
                    </tr>
                    <tr>
                        <th>College / Directorate</th><td>{{ optional($request->college)->name ?: optional(optional($request->employee)->college)->name ?: '-' }}</td>
                        <th>Department / Office / KVK</th><td>{{ optional($request->department)->name ?: optional(optional($request->employee)->department)->name ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Category</th>
                        <td>
                            {{ optional($request->category)->name ?: '-' }}
                            @if(optional($request->category)->item_group)<small class="text-muted">({{ optional($request->category)->item_group }})</small>@endif
                        </td>
                        <th>Priority</th><td><span class="badge badge-{{ $priorityClass($request->priority) }}">{{ $request->priority ?: 'Normal' }}</span></td>
                    </tr>
                    <tr>
                        <th>Item</th><td>{{ trim(($request->item_type ?: '').' - '.($request->item_name ?: ''), ' -') ?: '-' }}</td>
                        <th>Inventory No.</th><td>{{ $request->inventory_no ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Room</th><td>{{ $request->room_no ?: '-' }}</td>
                        <th>Asset</th><td>{{ optional($request->asset)->item_name ?: '-' }}</td>
                    </tr>
                    <tr><th>Default Problem</th><td colspan="3">{{ optional($request->problemTemplate)->title ?: '-' }}</td></tr>
                    <tr><th>Problem / Requirement</th><td colspan="3">{{ $request->problem_description ?: '-' }}</td></tr>
                    @if($request->attachment_url)
                        <tr><th>Attachment</th><td colspan="3"><a href="{{ $request->attachment_url }}" target="_blank" class="btn btn-sm btn-outline-primary">Open Attachment</a></td></tr>
                    @endif
                    @if($request->storekeeper_remarks)
                        <tr><th>Storekeeper Remarks</th><td colspan="3">{{ $request->storekeeper_remarks }}</td></tr>
                    @endif
                    @if($request->programmer_remarks || $request->programmer_work_done)
                        <tr><th>Verification / Work Done</th><td colspan="3">{{ $request->programmer_work_done ?: '-' }}</td></tr>
                        <tr>
                            <th>Programmer / Store Incharge Verification</th>
                            <td colspan="3">
                                <span class="badge badge-{{ $badgeClass($request->programmer_estimate_status) }}">{{ $request->programmer_estimate_status ?: 'Pending' }}</span><br>
                                {{ $request->programmer_remarks ?: '-' }}
                            </td>
                        </tr>
                    @endif
                    @if($request->d4_remarks)
                        <tr><th>D-4 Remarks</th><td colspan="3">{{ $request->d4_remarks }}</td></tr>
                    @endif
                </table>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <strong>Vendor Estimates / Quotations</strong>
                <small class="text-muted">PDF is mandatory for new estimate</small>
            </div>
            <div class="card-body table-responsive">
                <table class="table table-bordered table-sm mb-0">
                    <thead class="thead-light">
                        <tr><th>Selected</th><th>Vendor</th><th>Amount</th><th>Date</th><th>Details</th><th>Verification</th><th>File</th></tr>
                    </thead>
                    <tbody>
                    @forelse($request->estimates as $estimate)
                        <tr>
                            <td>{!! $estimate->is_selected ? '<span class="badge badge-success">Selected</span>' : '<span class="text-muted">-</span>' !!}</td>
                            <td>{{ optional($estimate->vendor)->name ?: '-' }}</td>
                            <td>{{ $estimate->estimate_amount ? 'Rs. '.number_format($estimate->estimate_amount,2) : '-' }}</td>
                            <td>{{ optional($estimate->estimate_date)->format('d-m-Y') ?: '-' }}</td>
                            <td>{{ $estimate->estimate_details ?: '-' }}</td>
                            <td>
                                <span class="badge badge-{{ $badgeClass($estimate->programmer_verification_status) }}">{{ $estimate->programmer_verification_status ?: 'Pending' }}</span>
                                @if($estimate->programmer_remarks)<br><small>{{ $estimate->programmer_remarks }}</small>@endif
                            </td>
                            <td>@if($estimate->estimate_file_url)<a class="btn btn-sm btn-outline-primary" target="_blank" href="{{ $estimate->estimate_file_url }}">Open PDF</a>@else - @endif</td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">No vendor estimate entered yet.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($canStorekeeperEstimate)
        <div class="card mb-3 rr-action-card">
            <div class="card-header bg-light" data-toggle="collapse" data-target="#storekeeperActionBox" aria-expanded="true">
                <strong>Storekeeper Action</strong>
                <small class="text-muted ml-2">Estimate → Verification → D-4 manual file</small>
            </div>
            <div id="storekeeperActionBox" class="collapse show">
                <div class="card-body">
                    <form id="storekeeperActionForm" method="POST" action="{{ route('repair-requests.storekeeper-action', $request) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="one_step_estimate_forward" value="1">

                        <div class="alert alert-info py-2">
                            Storekeeper should do only this flow:
                            <strong>save vendor estimate and send it to Programmer / Store Incharge</strong>.
                            After verification OK, print the sanction file and mark it submitted to D-4.
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="rr-required">Storekeeper Action</label>
                                <select id="storeAction" name="action" class="form-control" required>
                                    @if(!$request->selectedEstimate || $request->programmer_estimate_status != 'Estimate OK')
                                        <option value="forward_to_programmer">Save Estimate & Send to Programmer</option>
                                        <option value="forward_to_store_incharge">Save Estimate & Send to Store Incharge</option>
                                    @endif

                                    @if($request->selectedEstimate && $request->programmer_estimate_status == 'Estimate OK')
                                        <option value="mark_submitted_d4">Mark Printed File Submitted to D-4</option>
                                        <option value="sanction_received">Record Manual Sanction Received</option>
                                        <option value="sanction_rejected">Record Manual Sanction Rejected</option>
                                        <option value="work_completed">Mark Work Completed</option>
                                        <option value="close">Close Request</option>
                                    @endif

                                    <option value="reject">Reject Request</option>
                                </select>
                                <small class="rr-small-help">
                                    Use one action at a time. Estimate forwarding will show confirmation before submit.
                                </small>
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Select Existing Estimate</label>
                                <select id="selectedEstimate" name="selected_estimate_id" class="form-control">
                                    <option value="">New Estimate / No Change</option>
                                    @foreach($request->estimates as $estimate)
                                        <option value="{{ $estimate->id }}" {{ $request->selected_estimate_id == $estimate->id ? 'selected' : '' }}>
                                            {{ optional($estimate->vendor)->name }} - Rs. {{ number_format($estimate->estimate_amount,2) }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="rr-small-help">
                                    If selecting existing estimate, Vendor / Amount / PDF are not required again.
                                </small>
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Programmer Assign To</label>
                                <select id="assignedProgrammer" name="assigned_to_employee_id" class="form-control">
                                    <option value="">Auto Programmer</option>
                                    @foreach(($programmers ?? $employees) as $e)
                                        <option value="{{ $e->id }}">{{ $e->display_name }} - {{ $e->designation_name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Store Incharge Assign To</label>
                                <select id="assignedStoreIncharge" name="store_incharge_employee_id" class="form-control">
                                    <option value="">Auto Store Incharge</option>
                                    @foreach(($storeIncharges ?? collect()) as $e)
                                        <option value="{{ $e->id }}">{{ $e->display_name }} - {{ $e->designation_name }}</option>
                                    @endforeach
                                </select>
                                <small class="rr-small-help">Used only when sending to Store Incharge.</small>
                            </div>

                            <div class="col-md-12"><hr><strong>Vendor Allocation / Estimate</strong></div>

                            <div class="col-md-4 form-group">
                                <label id="vendorLabel">Vendor</label>
                                <select id="vendorId" name="vendor_id" class="form-control">
                                    <option value="">Select Vendor</option>
                                    @foreach($vendors as $v)
                                        <option value="{{ $v->id }}">{{ $v->name }} ({{ $v->vendor_type }})</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-4 form-group">
                                <label id="amountLabel">Estimate Amount</label>
                                <input id="estimateAmount" type="number" step="0.01" min="0" name="estimate_amount" class="form-control" placeholder="Example: 3500">
                            </div>

                            <div class="col-md-4 form-group">
                                <label>Estimate Date</label>
                                <input type="date" name="estimate_date" value="{{ date('Y-m-d') }}" class="form-control">
                            </div>

                            <div class="col-md-8 form-group">
                                <label>Estimate Details</label>
                                <textarea name="estimate_details" class="form-control" rows="2" placeholder="Part / material / service details"></textarea>
                            </div>

                            <div class="col-md-4 form-group">
                                <label id="pdfLabel">Upload Estimate / Quotation PDF</label>
                                <input id="estimateFile" type="file" name="estimate_file" accept="application/pdf" class="form-control-file">
                                <small class="rr-small-help">PDF is mandatory for new estimate.</small>
                            </div>

                            <div class="col-md-12 form-group">
                                <label class="rr-required">Storekeeper Remarks</label>
                                <textarea name="storekeeper_remarks" class="form-control" rows="2" required>{{ old('storekeeper_remarks', $request->storekeeper_remarks) }}</textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-success">
                            Submit Storekeeper Action
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        @if($canVerifyAndReturn)
        <div class="card mb-3 rr-action-card">
            <div class="card-header bg-light" data-toggle="collapse" data-target="#programmerActionBox" aria-expanded="true">
                <strong>{{ $verifierLabel }} Verification</strong>
                <small class="text-muted ml-2">Only verify and return to Storekeeper</small>
            </div>
            <div id="programmerActionBox" class="collapse show">
                <div class="card-body">
                    <form id="verificationReturnForm" method="POST" action="{{ route('repair-requests.programmer-action', $request) }}">
                        @csrf

                        <div class="alert alert-warning py-2">
                            Your role here is only to verify the selected estimate / repair requirement and return the request to Storekeeper.
                            Storekeeper will submit the file to D-4 after your verification.
                        </div>

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="rr-required">Verification Result</label>
                                <select name="action" class="form-control" required>
                                    <option value="">Select Result</option>
                                    <option value="estimate_ok">Estimate OK - Return to Storekeeper</option>
                                    <option value="estimate_not_ok">Estimate Not OK - Return to Storekeeper</option>
                                    <option value="need_revised_estimate">Need Revised Estimate - Return to Storekeeper</option>
                                </select>
                            </div>

                            <div class="col-md-8 form-group">
                                <label>Selected Estimate</label>
                                <input class="form-control" readonly value="@if($request->selectedEstimate){{ optional($request->selectedEstimate->vendor)->name }} - Rs. {{ number_format($request->selectedEstimate->estimate_amount,2) }}@else No estimate selected @endif">
                                @if($request->selectedEstimate && $request->selectedEstimate->estimate_file_url)
                                    <a href="{{ $request->selectedEstimate->estimate_file_url }}" target="_blank" class="btn btn-sm btn-outline-primary mt-2">Open Estimate PDF</a>
                                @endif
                            </div>

                            <div class="col-md-12 form-group">
                                <label>Physical Verification / Work Done / Observation</label>
                                <textarea name="programmer_work_done" class="form-control" rows="3" placeholder="Mention physical verification / repair observation / technical finding.">{{ old('programmer_work_done', $request->programmer_work_done) }}</textarea>
                            </div>

                            <div class="col-md-12 form-group">
                                <label class="rr-required">Verification Remarks</label>
                                <textarea name="programmer_remarks" class="form-control" rows="3" required placeholder="Example: Estimate verified and found OK / Estimate not justified / Revised estimate required.">{{ old('programmer_remarks', $request->programmer_remarks) }}</textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Submit Verification & Return to Storekeeper
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        @if($canD4Action)
        <div class="card mb-3 rr-action-card">
            <div class="card-header bg-light"
                 data-toggle="collapse"
                 data-target="#d4ActionBox"
                 aria-expanded="true">
                <strong>D-4 Seat Manual File Record</strong>
                <small class="text-muted ml-2">
                    Physical receipt → Print taken → Put up for sanction
                </small>
            </div>

            <div id="d4ActionBox" class="collapse show">
                <div class="card-body">

                    <div class="alert alert-info py-2">
                        <strong>D-4 workflow:</strong>
                        First mark the physical file as received. After taking the print,
                        record that the case has been put up before the competent authority
                        for financial sanction.
                    </div>

                    <form id="d4ActionForm"
                          method="POST"
                          action="{{ route('repair-requests.d4-action', $request) }}">

                        @csrf

                        <div class="row">
                            <div class="col-md-4 form-group">
                                <label class="rr-required">D-4 Action</label>

                                <select id="d4Action"
                                        name="action"
                                        class="form-control"
                                        required>

                                    <option value="">Select Action</option>

                                    @if($canD4MarkReceived)
                                        <option value="mark_received">
                                            Mark Physical File Received
                                        </option>
                                    @endif

                                    @if($canD4PutUp)
                                        <option value="sanction_put_up">
                                            Print Taken & Put Up for Sanction
                                        </option>
                                    @endif

                                    <option value="add_note">
                                        Add D-4 Note Only
                                    </option>
                                </select>

                                <small class="rr-small-help">
                                    The put-up action becomes available after the physical
                                    file is marked as received.
                                </small>
                            </div>

                            <div class="col-md-8 form-group">
                                <label class="rr-required">D-4 Remarks</label>

                                <textarea name="d4_remarks"
                                          class="form-control"
                                          rows="3"
                                          required
                                          placeholder="Example: Print of financial sanction proforma and estimate taken. Case put up before the competent authority for sanction.">{{ old('d4_remarks', $request->d4_remarks) }}</textarea>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-dark">
                            Submit D-4 Action
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        @if($canManualUpdate)
        <div class="card mb-3 rr-action-card">
            <div class="card-header bg-light" data-toggle="collapse" data-target="#manualActionBox" aria-expanded="false">
                <strong>Admin / Director Manual Correction</strong>
                <small class="text-muted ml-2">Use only for correction.</small>
            </div>
            <div id="manualActionBox" class="collapse">
                <div class="card-body">
                    <form method="POST" action="{{ route('repair-requests.status', $request) }}">
                        @csrf
                        <div class="row">
                            <div class="col-md-3 form-group"><label class="rr-required">Status</label><input name="status" class="form-control" value="{{ $request->status }}" required></div>
                            <div class="col-md-4 form-group"><label>Assign To</label><select name="assigned_to_employee_id" class="form-control"><option value="">No Change</option>@foreach($employees as $e)<option value="{{ $e->id }}">{{ $e->display_name }} - {{ $e->designation_name }}</option>@endforeach</select></div>
                            <div class="col-md-5 form-group"><label>Remarks</label><input name="remarks" class="form-control"></div>
                        </div>
                        <button type="submit" class="btn btn-warning">Manual Update</button>
                    </form>
                </div>
            </div>
        </div>
        @endif

        @if($canFeedback)
        <div class="card mb-3">
            <div class="card-header bg-white"><strong>Employee Confirmation / Reopen</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('repair-requests.feedback', $request) }}">
                    @csrf
                    <label class="rr-required">Feedback</label>
                    <textarea name="employee_feedback" class="form-control mb-2" placeholder="Feedback" required>{{ old('employee_feedback', $request->employee_feedback) }}</textarea>
                    <button type="submit" name="action" value="confirm" class="btn btn-success">Confirm & Close</button>
                    <button type="submit" name="action" value="reopen" class="btn btn-warning">Reopen</button>
                </form>
            </div>
        </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="rr-sticky-actions">
            <div class="card mb-3">
                <div class="card-header bg-white"><strong>Financial Sanction Summary</strong></div>
                <div class="card-body">
                    <p class="mb-2"><strong>Vendor:</strong> {{ $request->selected_vendor_name ?: '-' }}</p>
                    <p class="mb-2"><strong>Estimate:</strong> {{ $request->proforma_amount ? 'Rs. '.number_format($request->proforma_amount,2) : '-' }}</p>
                    <p class="mb-2"><strong>Verification:</strong> <span class="badge badge-{{ $badgeClass($request->programmer_estimate_status) }}">{{ $request->programmer_estimate_status ?: 'Pending' }}</span></p>
                    <p class="mb-2"><strong>Manual Sanction:</strong> {{ $request->manual_sanction_status ?: '-' }}</p>
                    <p class="mb-2"><strong>D-4 Submitted:</strong> {{ optional($request->d4_submitted_at)->format('d-m-Y h:i A') ?: '-' }}</p>
                    <p class="mb-3"><strong>D-4 Received:</strong> {{ optional($request->d4_received_at)->format('d-m-Y h:i A') ?: '-' }}</p>
                    @if($canPrintProforma)
                        <a class="btn btn-outline-primary btn-block" target="_blank" href="{{ route('repair-requests.proforma', $request) }}">Print Proforma</a>
                    @else
                        <button type="button" class="btn btn-outline-secondary btn-block" disabled>Print enabled after verification OK</button>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-white"><strong>History / Track</strong></div>
                <div class="card-body rr-timeline">
                    @forelse($request->logs as $log)
                        <div class="rr-timeline-item border-bottom mb-2 pb-2">
                            <strong>{{ $log->action }}</strong><br>
                            <small class="text-muted">
                                {{ $log->old_status ?: '-' }} → {{ $log->new_status ?: '-' }}<br>
                                {{ optional($log->user)->name ?: '-' }} | {{ optional($log->created_at)->format('d-m-Y h:i A') ?: '-' }}
                            </small>
                            @if($log->remarks)<div class="mt-1">{{ $log->remarks }}</div>@endif
                        </div>
                    @empty
                        <p class="text-muted mb-0">No history found.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function(){
    function setRequiredLabel(labelSelector, isRequired) {
        var label = $(labelSelector);
        if (isRequired) { label.addClass('rr-required'); }
        else { label.removeClass('rr-required'); }
    }

    function updateStorekeeperMandatoryFields() {
        if (!$('#storeAction').length) { return; }

        var action = $('#storeAction').val();
        var selectedEstimate = $('#selectedEstimate').val();

        var isForwardAction = ['forward_to_programmer', 'forward_to_store_incharge'].indexOf(action) >= 0;
        var newEstimateRequired = isForwardAction && !selectedEstimate;

        $('#vendorId').prop('required', newEstimateRequired);
        $('#estimateAmount').prop('required', newEstimateRequired);
        $('#estimateFile').prop('required', newEstimateRequired);

        setRequiredLabel('#vendorLabel', newEstimateRequired);
        setRequiredLabel('#amountLabel', newEstimateRequired);
        setRequiredLabel('#pdfLabel', newEstimateRequired);

        $('#assignedProgrammer').closest('.form-group').toggle(action === 'forward_to_programmer');
        $('#assignedStoreIncharge').closest('.form-group').toggle(action === 'forward_to_store_incharge');

        var showEstimateFields = isForwardAction || action === 'reject';
        $('#vendorId').closest('.form-group').toggle(isForwardAction);
        $('#estimateAmount').closest('.form-group').toggle(isForwardAction);
        $('#estimateFile').closest('.form-group').toggle(isForwardAction);
        $('textarea[name="estimate_details"]').closest('.form-group').toggle(isForwardAction);
        $('input[name="estimate_date"]').closest('.form-group').toggle(isForwardAction);
    }

    $('#storeAction, #selectedEstimate').on('change', updateStorekeeperMandatoryFields);
    updateStorekeeperMandatoryFields();

    $('#storekeeperActionForm').on('submit', function(e){
        var action = $('#storeAction').val();

        if (['forward_to_programmer', 'forward_to_store_incharge'].indexOf(action) >= 0) {
            var label = action === 'forward_to_programmer' ? 'Programmer' : 'Store Incharge';
            if (!confirm('Confirm: vendor estimate will be saved and sent to ' + label + ' for verification. Continue?')) {
                e.preventDefault();
                return false;
            }
        }

        if (action === 'mark_submitted_d4') {
            if (!confirm('Confirm: printed financial sanction file is submitted manually to D-4?')) {
                e.preventDefault();
                return false;
            }
        }
    });

    $('#verificationReturnForm').on('submit', function(e){
        if (!confirm('Confirm: submit verification result and return request to Storekeeper?')) {
            e.preventDefault();
            return false;
        }
    });

    $('#d4ActionForm').on('submit', function(e){
        var action = $('#d4Action').val();

        if (action === 'mark_received') {
            if (!confirm('Confirm that the physical financial sanction file has been received at D-4?')) {
                e.preventDefault();
                return false;
            }
        }

        if (action === 'sanction_put_up') {
            if (!confirm('Confirm that the print has been taken and the case has been put up for financial sanction?')) {
                e.preventDefault();
                return false;
            }
        }
    });
})();
</script>
@endpush