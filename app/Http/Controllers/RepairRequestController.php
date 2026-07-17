<?php

namespace App\Http\Controllers;

use App\Asset;
use App\Employee;
use App\ProblemTemplate;
use App\RepairCategory;
use App\RepairEstimate;
use App\RepairLog;
use App\RepairRequest;
use App\Vendor;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\AccessScope;

class RepairRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

   public function index(Request $request)
{
    $user = Auth::user();
    $employee = $user ? $user->employee : null;
    $handler = $request->get('handler');

    /*
    |--------------------------------------------------------------------------
    | Handler guard
    |--------------------------------------------------------------------------
    | Programmer must not open Storekeeper queue.
    | Typo like handler=strorekeeper is invalid.
    */
    $allowedHandlers = ['storekeeper', 'programmer', 'd4_seat'];

    if ($handler && !in_array($handler, $allowedHandlers, true)) {
        return redirect()
            ->route('repair-requests.index')
            ->with('error', 'Invalid request queue selected.');
    }

    if ($handler === 'storekeeper' && !$user->hasAnyRole(['superuser', 'storekeeper'])) {
        abort(403, 'Only Storekeeper can view the Storekeeper pending queue.');
    }

    if ($handler === 'programmer') {
        $isStoreIncharge = $employee && $this->employeeHasCharge($employee, 'Store Incharge');

        if (!$user->hasAnyRole(['superuser', 'programmer']) && !$isStoreIncharge) {
            abort(403, 'Only Programmer / Store Incharge can view pending verification.');
        }
    }

    if ($handler === 'd4_seat' && !$user->hasAnyRole(['superuser', 'd4_seat'])) {
        abort(403, 'Only D-4 Seat can view D-4 pending files.');
    }

    $baseQuery = RepairRequest::with([
        'employee',
        'category',
        'assignedTo',
        'selectedEstimate.vendor'
    ]);

    /*
    |--------------------------------------------------------------------------
    | Normal access scope
    |--------------------------------------------------------------------------
    | Superuser should see all on /repair-requests.
    | Do not auto-filter just because the same user is also Programmer.
    */
    if (AccessScope::isEmployeeOnly($user)) {
        if ($employee) {
            $baseQuery->where('employee_id', $employee->id);
        } else {
            $baseQuery->whereRaw('1 = 0');
        }
    } else {
        AccessScope::apply($baseQuery);
    }

    if ($request->filled('college_id')) {
        $baseQuery->where('college_id', $request->college_id);
    }

    if ($request->filled('department_id')) {
        $baseQuery->where('department_id', $request->department_id);
    }

    if ($request->filled('status')) {
        $baseQuery->where('status', $request->status);
    }

    /*
    |--------------------------------------------------------------------------
    | Handler filters
    |--------------------------------------------------------------------------
    | These filters apply only when handler is selected.
    */
    if ($handler === 'programmer') {
        $baseQuery->whereIn('current_handler_role', ['programmer', 'store_incharge']);

        // For Programmer-only user, keep only requests assigned to him OR his handler queue.
        // For Superuser, AccessScope normally allows all, but this filter still keeps only verification queue.
        if (!$user->hasRole('superuser') && $employee) {
            $baseQuery->where(function ($q) use ($employee) {
                $q->where('assigned_to_employee_id', $employee->id)
                  ->orWhereIn('current_handler_role', ['programmer', 'store_incharge']);
            });
        }
    } elseif ($handler === 'storekeeper') {
        $baseQuery->where(function ($q) {
            $q->where('current_handler_role', 'storekeeper')
              ->orWhere('status', 'Submitted to Storekeeper');
        });

        if (!$user->hasRole('superuser') && $employee) {
            $baseQuery->where(function ($q) use ($employee) {
                $q->where('assigned_to_employee_id', $employee->id)
                  ->orWhere('current_handler_role', 'storekeeper')
                  ->orWhere('status', 'Submitted to Storekeeper');
            });
        }
    } elseif ($handler === 'd4_seat') {
        $baseQuery->where(function ($q) {
            $q->where('current_handler_role', 'd4_seat')
              ->orWhereIn('manual_sanction_status', [
                  'Submitted to D-4',
                  'Received at D-4'
              ]);
        });

        if (!$user->hasRole('superuser') && $employee) {
            $baseQuery->where(function ($q) use ($employee) {
                $q->where('assigned_to_employee_id', $employee->id)
                  ->orWhere('current_handler_role', 'd4_seat')
                  ->orWhereIn('manual_sanction_status', [
                      'Submitted to D-4',
                      'Received at D-4'
                  ]);
            });
        }
    }

    $requests = $baseQuery
        ->latest()
        ->paginate(20)
        ->appends($request->query());

    $colleges = AccessScope::colleges();
    $departments = AccessScope::departments();

    return view('repair_requests.index', compact('requests', 'colleges', 'departments'));
}


    public function create()
    {
        $user = Auth::user();
        $employee = $user->employee;
        if (!$employee && AccessScope::isEmployeeOnly($user)) {
            return redirect()->route('home')->withErrors('Employee profile not found. Contact admin.');
        }

        $categories = RepairCategory::where('is_active',1)->orderBy('item_group')->orderBy('name')->get();
        $problemTemplates = ProblemTemplate::where('is_active',1)->orderBy('title')->get();
        $employees = AccessScope::employeesQuery()->orderBy('first_name')->get();

        // Employee sees only assets allocated to him/her. Storekeeper/admin/director can select employee first.
        if (AccessScope::isEmployeeOnly($user)) {
            $assets = Asset::where('assigned_to_employee_id', $employee->id)
                ->where('asset_state', 'With Employee')
                ->orderBy('item_name')
                ->get();
        } else {
            $assets = collect();
        }

        return view('repair_requests.create', compact('categories','assets','employees','employee','problemTemplates'));
    }

    public function problemTemplatesByCategory(RepairCategory $category)
    {
        $items = ProblemTemplate::where('is_active',1)
            ->where(function($q) use ($category) {
                $q->where('repair_category_id', $category->id)
                  ->orWhereNull('repair_category_id')
                  ->orWhere('item_group', $category->item_group);
            })
            ->orderBy('title')
            ->get(['id','title','description']);
        return response()->json($items);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        $currentEmployee = $user ? $user->employee : null;

        /*
         * Auto-fill employee for request creation.
         *
         * This is needed when logged-in users have more than one role, for example:
         * Department Admin + Employee, Storekeeper + Employee, Programmer + Employee.
         * The create form may not send employee_id, so we default to the logged-in user's
         * linked employee record. Employee-only users are always forced to their own record.
         */
        if (AccessScope::isEmployeeOnly($user) && $currentEmployee) {
            $request->merge([
                'employee_id' => $currentEmployee->id,
            ]);
        } elseif (!$request->filled('employee_id') && $currentEmployee) {
            $request->merge([
                'employee_id' => $currentEmployee->id,
            ]);
        }

        $data = $request->validate([
            'employee_id' => 'nullable|exists:employees,id',
            'repair_category_id' => 'nullable|exists:repair_categories,id',
            'asset_id' => 'nullable|exists:assets,id',
            'problem_template_id' => 'required|exists:problem_templates,id',
            'item_type' => 'nullable|string|max:255',
            'item_name' => 'nullable|string|max:255',
            'inventory_no' => 'nullable|string|max:255',
            'room_no' => 'nullable|string|max:100',
            'priority' => 'required|in:Normal,Urgent',
            'problem_description' => 'nullable|string',
            'attachment' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        $employeeId = isset($data['employee_id']) ? $data['employee_id'] : null;
        $data['employee_id'] = $employeeId;
        $requestingEmployee = Employee::find($employeeId);
        if ($requestingEmployee && !AccessScope::canAccessEmployee($requestingEmployee, $user)) {
            abort(403, 'Selected employee is outside your scope.');
        }
        if (!$data['employee_id'] || !$requestingEmployee) {
            return back()->withErrors('Employee is required.')->withInput();
        }

        $data['college_id'] = $requestingEmployee->college_id;
        $data['department_id'] = $requestingEmployee->department_id;
        $data['room_no'] = $data['room_no'] ?: $requestingEmployee->room_no;

        // If an allocated asset is selected, fill request fields automatically from asset.
        if (!empty($data['asset_id'])) {
            $asset = Asset::find($data['asset_id']);
            if (!$asset) return back()->withErrors('Selected asset was not found.')->withInput();

            if (AccessScope::isEmployeeOnly($user) && ($asset->assigned_to_employee_id != $requestingEmployee->id || $asset->asset_state != 'With Employee')) {
                abort(403, 'You can submit request only for your allocated assets.');
            }

            // Do not trust browser values when an asset is selected.
            // Item Type, Item Name, Inventory No. and Room No. are always taken from the allocated asset.
            $data['item_type'] = $asset->asset_category;
            $data['item_name'] = trim($asset->item_name.' '.($asset->make ? '(' . $asset->make . ' ' . $asset->model . ')' : ''));
            $data['inventory_no'] = $asset->inventory_no;
            $data['room_no'] = $asset->location ?: $requestingEmployee->room_no;

            if (empty($data['repair_category_id'])) {
                $data['repair_category_id'] = $this->categoryIdForAsset($asset);
            }
        }

        if (empty($data['repair_category_id'])) {
            $general = RepairCategory::where('name','General Repair')->orWhere('name','General')->first();
            if (!$general) return back()->withErrors('Please select category.')->withInput();
            $data['repair_category_id'] = $general->id;
        }

        // Default Problem / Requirement is mandatory. The description is filled from the selected template if blank.
        $template = ProblemTemplate::find($data['problem_template_id']);
        if ($template && empty(trim($data['problem_description'] ?? ''))) {
            $data['problem_description'] = $template->description ?: $template->title;
        }

        if (empty(trim($data['problem_description'] ?? ''))) {
            return back()->withErrors('Problem / Material Requirement could not be prepared from selected default problem.')->withInput();
        }

        if ($request->hasFile('attachment')) {
            $data['attachment'] = $request->file('attachment')->store('repair_attachments','public');
        }

        // Official workflow: every request first goes to Storekeeper.
        $storekeeper = $this->findEmployeeByRole('storekeeper');
        $data['request_no'] = $this->generateRequestNo();
        $data['status'] = 'Submitted to Storekeeper';
        $data['current_handler_role'] = 'storekeeper';
        $data['assigned_to_employee_id'] = optional($storekeeper)->id;
        $data['storekeeper_received_at'] = Carbon::now();
        $data['manual_sanction_status'] = 'Not Submitted';

        $requestModel = RepairRequest::create($data);
        $this->log($requestModel, 'Request Submitted', null, $requestModel->status, 'Employee request submitted. Marked to Storekeeper first.');

        return redirect()->route('repair-requests.show', $requestModel)->with('success', 'Request submitted. It has gone to Storekeeper first.');
    }
public function show(RepairRequest $repair_request)
{
    $this->authorizeView($repair_request);

    $repair_request->load([
        'employee.college',
        'employee.department',
        'college',
        'department',
        'category',
        'asset',
        'problemTemplate',
        'assignedTo',
        'logs.user',
        'programmer',
        'storekeeper',
        'd4Receiver',
        'proformaGeneratedBy',
        'estimates.vendor',
        'estimates.enteredBy',
        'estimates.programmer',
        'selectedEstimate.vendor'
    ]);

    $user = Auth::user();
    $employee = $user ? $user->employee : null;
    $isStoreIncharge = $this->employeeHasCharge($employee, 'Store Incharge');

    $isSuperuser = $user && method_exists($user, 'hasRole') && $user->hasRole('superuser');
    $canStorekeeperAction = $isSuperuser || ($user && method_exists($user, 'hasRole') && $user->hasRole('storekeeper'));
    $canProgrammerAction = $isSuperuser
        || ($user && method_exists($user, 'hasRole') && ($user->hasRole('programmer') || $user->hasRole('store_incharge')))
        || $isStoreIncharge
        || ($employee && $repair_request->assigned_to_employee_id == $employee->id && in_array($repair_request->current_handler_role, ['programmer','store_incharge']));
    $canManualUpdate = $isSuperuser || ($user && method_exists($user, 'hasAnyRole') && $user->hasAnyRole(['admin','director']));

    $employees = collect();
    $programmers = collect();
    $storeIncharges = collect();
    $vendors = collect();

    if ($canStorekeeperAction || $canManualUpdate) {
        $employees = AccessScope::employeesQuery()
            ->with(['user','activeCharges'])
            ->orderBy('first_name')
            ->get();
    }

    if ($canStorekeeperAction) {
        $programmers = AccessScope::apply(Employee::whereHas('user', function($q) {
            $q->where('is_active', 1)->where(function($x) {
                $x->where('role', 'programmer')
                  ->orWhereHas('roles', function($r) { $r->where('name', 'programmer')->orWhere('slug', 'programmer'); });
            });
        }))->orderBy('first_name')->get();

        $storeIncharges = AccessScope::apply(Employee::whereHas('activeCharges', function($q) {
            $q->where('charge_name', 'Store Incharge');
        }))->orderBy('first_name')->get();

        $vendors = Vendor::where('is_active', 1)->orderBy('name')->get();
    }

    return view('repair_requests.show', [
        'request' => $repair_request,
        'employees' => $employees,
        'programmers' => $programmers,
        'storeIncharges' => $storeIncharges,
        'vendors' => $vendors,
    ]);
}

    public function storekeeperAction(Request $request, RepairRequest $repair_request)
    {
        $user = Auth::user();
        if (!$user->hasRole('superuser') && !$user->hasRole('storekeeper')) abort(403);

        if (!AccessScope::canAccessDepartment($repair_request->department_id, $user)) abort(403);

        $data = $request->validate([
            'action' => 'required|in:save_estimate,forward_to_programmer,forward_to_store_incharge,generate_proforma,mark_submitted_d4,sanction_received,sanction_rejected,work_completed,close,reject',
            'selected_estimate_id' => 'nullable|exists:repair_estimates,id',
            'vendor_id' => 'nullable|exists:vendors,id',
            'estimate_amount' => 'nullable|numeric|min:0',
            'estimate_date' => 'nullable|date',
            'estimate_details' => 'nullable|string',
            'estimate_file' => 'nullable|file|mimes:pdf|max:4096',
            'assigned_to_employee_id' => 'nullable|exists:employees,id',
            'store_incharge_employee_id' => 'nullable|exists:employees,id',
            'storekeeper_remarks' => 'required|string',
        ]);

        $old = $repair_request->status;
        $empId = optional($user->employee)->id;

        $repair_request->storekeeper_verified_by = $empId;
        $repair_request->storekeeper_verified_at = Carbon::now();
        $repair_request->storekeeper_remarks = $data['storekeeper_remarks'];

        if ((!empty($data['vendor_id']) && empty($data['estimate_amount'])) || (empty($data['vendor_id']) && !empty($data['estimate_amount']))) {
            return back()->withErrors('Please select vendor and enter estimate amount together.')->withInput();
        }

        $isCreatingNewEstimate = !empty($data['vendor_id']) && !empty($data['estimate_amount']);
        if ($isCreatingNewEstimate && !$request->hasFile('estimate_file')) {
            return back()->withErrors('Please upload estimate / quotation PDF for the selected vendor estimate.')->withInput();
        }

        $createdEstimate = $this->saveEstimateFromStorekeeper($repair_request, $data, $request);
        if (!$createdEstimate && !empty($data['selected_estimate_id'])) {
            $estimate = RepairEstimate::where('repair_request_id', $repair_request->id)->where('id', $data['selected_estimate_id'])->first();
            if ($estimate) {
                RepairEstimate::where('repair_request_id', $repair_request->id)->update(['is_selected' => 0]);
                $estimate->is_selected = 1;
                $estimate->save();
                $repair_request->selected_estimate_id = $estimate->id;
            }
        }


        if ($data['action'] == 'save_estimate') {
            if (!$repair_request->selected_estimate_id) {
                return back()->withErrors('Please select vendor and enter estimated amount, or select an existing estimate.')->withInput();
            }
            $repair_request->assigned_to_employee_id = $empId;
            $repair_request->current_handler_role = 'storekeeper';
            $repair_request->status = 'Estimate Taken by Storekeeper';
            $message = 'Vendor estimate saved by Storekeeper.';
        } elseif ($data['action'] == 'forward_to_programmer') {
            if (!$repair_request->selected_estimate_id) {
                return back()->withErrors('Please save/select vendor estimate before forwarding to Programmer.')->withInput();
            }
            $assigned = !empty($data['assigned_to_employee_id']) ? Employee::find($data['assigned_to_employee_id']) : $this->findEmployeeByRole('programmer');
            $repair_request->assigned_to_employee_id = optional($assigned)->id;
            $repair_request->current_handler_role = 'programmer';
            $repair_request->status = 'Sent to Programmer for Verification';
            $repair_request->forwarded_to_programmer_at = Carbon::now();
            $repair_request->programmer_estimate_status = 'Pending';
            $message = 'Vendor estimate forwarded to Programmer for physical/technical verification.';
        } elseif ($data['action'] == 'forward_to_store_incharge') {
            if (!$repair_request->selected_estimate_id) {
                return back()->withErrors('Please save/select vendor estimate before forwarding to Store Incharge.')->withInput();
            }
            $assigned = !empty($data['store_incharge_employee_id']) ? Employee::find($data['store_incharge_employee_id']) : $this->findEmployeeByCharge('Store Incharge');
            if (!$assigned) {
                return back()->withErrors('No Store Incharge found. Please assign Store Incharge charge to an employee first.')->withInput();
            }
            $repair_request->assigned_to_employee_id = $assigned->id;
            $repair_request->current_handler_role = 'store_incharge';
            $repair_request->status = 'Sent to Store Incharge for Verification';
            $repair_request->forwarded_to_programmer_at = Carbon::now();
            $repair_request->programmer_estimate_status = 'Pending';
            $message = 'Vendor estimate forwarded to Store Incharge for verification/approval remarks.';
        } elseif ($data['action'] == 'generate_proforma') {
            if ($repair_request->programmer_estimate_status != 'Estimate OK') {
                return back()->withErrors('Programmer or Store Incharge must verify estimate as OK before proforma generation.')->withInput();
            }
            $this->prepareProformaFromEstimate($repair_request);
            $repair_request->current_handler_role = 'storekeeper';
            $repair_request->assigned_to_employee_id = $empId;
            $repair_request->status = 'Financial Sanction Proforma Ready';
            $message = 'Financial sanction proforma is ready for print.';
        } elseif ($data['action'] == 'mark_submitted_d4') {
            if ($repair_request->programmer_estimate_status != 'Estimate OK' || !$repair_request->selected_estimate_id) {
                return back()->withErrors('Financial Sanction Proforma can be submitted to D-4 only after Programmer / Store Incharge verifies the selected estimate as OK.')->withInput();
            }
            $this->prepareProformaFromEstimate($repair_request);
            $d4 = $this->findEmployeeByRole('d4_seat');
            $repair_request->current_handler_role = 'd4_seat';
            $repair_request->assigned_to_employee_id = optional($d4)->id;
            $repair_request->status = 'Submitted Manually to D-4';
            $repair_request->manual_sanction_status = 'Submitted to D-4';
            $repair_request->proforma_printed_at = $repair_request->proforma_printed_at ?: Carbon::now();
            $repair_request->d4_submitted_at = Carbon::now();
            $message = 'Printed financial sanction file marked as submitted manually to D-4 seat.';
        } elseif ($data['action'] == 'sanction_received') {
            $repair_request->current_handler_role = 'storekeeper';
            $repair_request->assigned_to_employee_id = $empId;
            $repair_request->status = 'Sanction Received';
            $repair_request->manual_sanction_status = 'Sanction Received';
            $message = 'Manual financial sanction received and recorded.';
        } elseif ($data['action'] == 'sanction_rejected') {
            $repair_request->current_handler_role = 'storekeeper';
            $repair_request->assigned_to_employee_id = $empId;
            $repair_request->status = 'Sanction Rejected';
            $repair_request->manual_sanction_status = 'Rejected';
            $message = 'Manual financial sanction rejection recorded.';
        } elseif ($data['action'] == 'work_completed') {
            $repair_request->current_handler_role = 'employee';
            $repair_request->assigned_to_employee_id = $repair_request->employee_id;
            $repair_request->status = 'Work Completed - Employee Confirmation Pending';
            $message = 'Work completed. Employee confirmation pending.';
        } elseif ($data['action'] == 'close') {
            $repair_request->current_handler_role = 'none';
            $repair_request->assigned_to_employee_id = null;
            $repair_request->status = 'Closed';
            $repair_request->closed_at = Carbon::now();
            $message = 'Closed by Storekeeper.';
        } else {
            $repair_request->current_handler_role = 'none';
            $repair_request->assigned_to_employee_id = null;
            $repair_request->status = 'Rejected';
            $message = 'Rejected by Storekeeper.';
        }

        $repair_request->save();
        $this->log($repair_request, 'Storekeeper Action', $old, $repair_request->status, $message.' '.$data['storekeeper_remarks']);

        return back()->with('success', $message);
    }

    public function programmerAction(Request $request, RepairRequest $repair_request)
    {
        $user = Auth::user();
        $employee = $user->employee;
        $isStoreIncharge = $this->employeeHasCharge($employee, 'Store Incharge');
        if (!$user->hasRole('superuser') && !$user->hasRole('programmer') && !$user->hasRole('store_incharge') && !$isStoreIncharge) abort(403);

        if (!AccessScope::canAccessDepartment($repair_request->department_id, $user)) abort(403);
        $verifierLabel = $isStoreIncharge && !$user->hasRole('programmer') ? 'Store Incharge' : 'Programmer';

        $data = $request->validate([
            'action' => 'required|in:receive,estimate_ok,estimate_not_ok,need_revised_estimate',
            'programmer_work_done' => 'nullable|string',
            'programmer_remarks' => 'required|string',
        ]);

        if (!$repair_request->selected_estimate_id) {
            return back()->withErrors('No selected estimate found for verification.')->withInput();
        }

        $old = $repair_request->status;
        $empId = optional($user->employee)->id;
        $estimate = $repair_request->selectedEstimate;

        $repair_request->programmer_verified_by = $empId;
        $repair_request->programmer_remarks = $data['programmer_remarks'];
        $repair_request->programmer_work_done = $data['programmer_work_done'];
        $repair_request->programmer_received_at = $repair_request->programmer_received_at ?: Carbon::now();

        $estimate->programmer_verified_by = $empId;
        $estimate->programmer_remarks = $data['programmer_remarks'];
        $estimate->programmer_verified_at = Carbon::now();

        $storekeeper = $this->findEmployeeByRole('storekeeper');
        $repair_request->assigned_to_employee_id = optional($storekeeper)->id;
        $repair_request->current_handler_role = 'storekeeper';
        
        if ($data['action'] == 'receive') {
            $repair_request->assigned_to_employee_id = $empId;
            $repair_request->current_handler_role = ($verifierLabel == 'Store Incharge') ? 'store_incharge' : 'programmer';
            $repair_request->status = $verifierLabel.' Received for Verification';
            $message = $verifierLabel.' received request for verification.';
        } elseif ($data['action'] == 'estimate_ok') {
            $estimate->programmer_verification_status = 'Estimate OK';
            $repair_request->programmer_estimate_status = 'Estimate OK';
            $repair_request->programmer_completed_at = Carbon::now();
            $repair_request->status = $verifierLabel.' Verified Estimate OK';
            $message = $verifierLabel.' verified item/repair/estimate and found it OK. Request returned to Storekeeper.';
        } elseif ($data['action'] == 'estimate_not_ok') {
            $estimate->programmer_verification_status = 'Estimate Not OK';
            $repair_request->programmer_estimate_status = 'Estimate Not OK';
            $repair_request->programmer_completed_at = Carbon::now();
            $repair_request->status = $verifierLabel.' Returned - Estimate Not OK';
            $message = $verifierLabel.' did not approve estimate. Request returned to Storekeeper.';
        } else {
            $estimate->programmer_verification_status = 'Need Revised Estimate';
            $repair_request->programmer_estimate_status = 'Need Revised Estimate';
            $repair_request->programmer_completed_at = Carbon::now();
            $repair_request->status = $verifierLabel.' Asked for Revised Estimate';
            $message = $verifierLabel.' asked Storekeeper for revised estimate.';
        }

        $estimate->save();
        $repair_request->save();
        $this->log($repair_request, $verifierLabel.' Verification Remarks', $old, $repair_request->status, $message.' '.$data['programmer_remarks']);

        return back()->with('success', $message);
    }

    public function d4Action(Request $request, RepairRequest $repair_request)
    {
        $user = Auth::user();
if (!$user->isRole(['admin','college_admin','department_admin','director','d4_seat'])) abort(403);
        if (!AccessScope::canAccessDepartment($repair_request->department_id, $user)) abort(403);

        $data = $request->validate([
            'action' => 'required|in:mark_received,add_note',
            'd4_remarks' => 'required|string',
        ]);

        $old = $repair_request->status;
        $empId = optional($user->employee)->id;
        $repair_request->d4_remarks = $data['d4_remarks'];

        if ($data['action'] == 'mark_received') {
            $repair_request->d4_received_by = $empId;
            $repair_request->d4_received_at = Carbon::now();
            $repair_request->current_handler_role = 'd4_seat';
            $repair_request->assigned_to_employee_id = $empId;
            $repair_request->status = 'D-4 Received Manual File';
            $repair_request->manual_sanction_status = 'Received at D-4';
            $message = 'D-4 seat marked manual file as received.';
        } else {
            $message = 'D-4 note added.';
        }

        $repair_request->save();
        $this->log($repair_request, 'D-4 Action', $old, $repair_request->status, $message.' '.$data['d4_remarks']);
        return back()->with('success', $message);
    }

    public function updateStatus(Request $request, RepairRequest $repair_request)
    {
        $user = Auth::user();
if (!$user->hasRole('superuser') && !$user->hasRole('admin') && !$user->hasRole('director')) abort(403);
        if (!AccessScope::canAccessDepartment($repair_request->department_id, $user)) abort(403);

        $data = $request->validate([
            'status' => 'required|string|max:255',
            'assigned_to_employee_id' => 'nullable|exists:employees,id',
            'store_incharge_employee_id' => 'nullable|exists:employees,id',
            'remarks' => 'nullable|string',
        ]);

        $old = $repair_request->status;
        $repair_request->status = $data['status'];
        if (!empty($data['assigned_to_employee_id'])) $repair_request->assigned_to_employee_id = $data['assigned_to_employee_id'];
        if ($data['status'] == 'Closed') $repair_request->closed_at = Carbon::now();
        $repair_request->save();

        $this->log($repair_request, 'Manual Status Correction', $old, $repair_request->status, $data['remarks'] ?? null);
        return redirect()->route('repair-requests.show', $repair_request)->with('success', 'Status updated.');
    }

    public function employeeFeedback(Request $request, RepairRequest $repair_request)
    {
        $user = Auth::user();
        if (AccessScope::isEmployeeOnly($user) && optional($user->employee)->id != $repair_request->employee_id) abort(403);

        $data = $request->validate([
            'employee_feedback' => 'required|string',
            'action' => 'required|in:confirm,reopen',
        ]);

        $old = $repair_request->status;
        $repair_request->employee_feedback = $data['employee_feedback'];
        if ($data['action'] == 'confirm') {
            $repair_request->status = 'Closed';
            $repair_request->current_handler_role = 'none';
            $repair_request->assigned_to_employee_id = null;
            $repair_request->closed_at = Carbon::now();
        } else {
            $repair_request->status = 'Reopened';
            $repair_request->current_handler_role = 'storekeeper';
            $repair_request->assigned_to_employee_id = optional($this->findEmployeeByRole('storekeeper'))->id;
        }
        $repair_request->save();

        $this->log($repair_request, 'Employee Feedback', $old, $repair_request->status, $data['employee_feedback']);
        return back()->with('success', 'Feedback submitted.');
    }

    public function proforma(RepairRequest $repair_request)
    {
        $this->authorizeView($repair_request);
        $repair_request->load(['employee.department','department','category','asset','assignedTo','storekeeper','programmer','proformaGeneratedBy','selectedEstimate.vendor','selectedEstimate.programmer']);

        if ($repair_request->programmer_estimate_status != 'Estimate OK' || !$repair_request->selectedEstimate) {
            return redirect()->route('repair-requests.show', $repair_request)
                ->withErrors('Financial Sanction Proforma can be printed only after Programmer / Store Incharge verifies the selected estimate as OK.');
        }

        if (!$repair_request->proforma_generated_at) {
            $this->prepareProformaFromEstimate($repair_request);
            $repair_request->save();
        }

        return view('repair_requests.proforma', ['request' => $repair_request]);
    }

    private function generateRequestNo()
    {
        $year = date('Y');
        $count = RepairRequest::whereYear('created_at', $year)->count() + 1;
        return 'DEE-REP-'.$year.'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    private function saveEstimateFromStorekeeper(RepairRequest $repairRequest, array $data, Request $httpRequest)
    {
        if (empty($data['vendor_id']) || empty($data['estimate_amount'])) {
            return null;
        }

        $estimateData = [
            'repair_request_id' => $repairRequest->id,
            'vendor_id' => $data['vendor_id'],
            'estimate_amount' => $data['estimate_amount'],
            'estimate_date' => $data['estimate_date'] ?: date('Y-m-d'),
            'estimate_details' => $data['estimate_details'],
            'is_selected' => 1,
            'entered_by' => optional(Auth::user()->employee)->id,
        ];

        if ($httpRequest->hasFile('estimate_file')) {
            $estimateData['estimate_file'] = $httpRequest->file('estimate_file')->store('vendor_estimates','public');
        }

        RepairEstimate::where('repair_request_id', $repairRequest->id)->update(['is_selected' => 0]);
        $estimate = RepairEstimate::create($estimateData);
        $repairRequest->selected_estimate_id = $estimate->id;
        $repairRequest->financial_sanction_amount = $estimate->estimate_amount;
        $repairRequest->enclosure_details = $repairRequest->enclosure_details ?: 'Estimate / quotation PDF from '.$estimate->vendor->name;
        return $estimate;
    }

    private function prepareProformaFromEstimate(RepairRequest $repairRequest)
    {
        $estimate = $repairRequest->selectedEstimate;
        if ($estimate) {
            $repairRequest->financial_sanction_amount = $estimate->estimate_amount;
            if (!$repairRequest->enclosure_details) {
                $repairRequest->enclosure_details = 'Estimate / quotation PDF from '.$estimate->vendor->name;
            }
        }
        $repairRequest->requires_financial_sanction = 1;
        $repairRequest->proforma_date = $repairRequest->proforma_date ?: Carbon::today();
        $repairRequest->proforma_generated_by = $repairRequest->proforma_generated_by ?: optional(Auth::user()->employee)->id;
        $repairRequest->proforma_generated_at = $repairRequest->proforma_generated_at ?: Carbon::now();
        // Proforma fields are prepared automatically from request and selected estimate.
        // Storekeeper no longer fills scheme/purpose/enclosure/approval remarks manually.
        $repairRequest->financial_sanction_purpose = $this->defaultPurpose($repairRequest);
        $repairRequest->purchase_payment_type = 'purchase / payment of material / repair';
        $repairRequest->vehicle_no = $repairRequest->inventory_no;
    }


    private function categoryIdForAsset(Asset $asset)
    {
        $name = $asset->asset_category;
        $map = [
            'Computer' => 'Computer',
            'Printer' => 'Printer',
            'Scanner' => 'Computer',
            'UPS' => 'UPS / Battery',
            'Chair' => 'Furniture',
            'Table' => 'Furniture',
            'Sound System' => 'Electrical',
            'Speaker' => 'Electrical',
            'Webcam' => 'Computer',
            'Projector' => 'Computer',
            'Furniture' => 'Furniture',
            'Electrical' => 'Electrical',
        ];
        $categoryName = isset($map[$name]) ? $map[$name] : 'General Repair';
        $category = RepairCategory::where('name', $categoryName)->first();
        return optional($category)->id;
    }

    private function defaultPurpose(RepairRequest $repairRequest)
    {
        $parts = [];
        if ($repairRequest->item_name) $parts[] = $repairRequest->item_name;
        if ($repairRequest->inventory_no) $parts[] = '(Inventory No. '.$repairRequest->inventory_no.')';
        if ($repairRequest->selected_vendor_name) $parts[] = 'as per estimate of '.$repairRequest->selected_vendor_name;
        return trim(implode(' ', $parts)) ?: $repairRequest->problem_description;
    }

    private function findEmployeeByCharge($chargeName)
    {
        $q = Employee::whereHas('activeCharges', function($q) use ($chargeName) {
            $q->where('charge_name', $chargeName);
        });
        return AccessScope::apply($q)->first();
    }

    private function employeeHasCharge($employee, $chargeName)
    {
        return $employee && $employee->hasActiveCharge($chargeName);
    }

    private function findEmployeeByRole($role)
    {
        $q = Employee::whereHas('user', function($q) use ($role) {
            $q->where('is_active', 1)->where(function($x) use ($role) {
                $x->where('role', $role)
                  ->orWhereHas('roles', function($r) use ($role) {
                      $r->where('name', $role);
                  });
            });
        });
        return AccessScope::apply($q)->first();
    }

    private function authorizeView(RepairRequest $request)
    {
        $user = Auth::user();
        if ($user->isRole(['admin','college_admin','department_admin','director','storekeeper','programmer','d4_seat']) && AccessScope::canAccessDepartment($request->department_id, $user)) return true;
        if ($this->employeeHasCharge($user->employee, 'Store Incharge')) return true;
        if ($user->hasRole('employee') && optional($user->employee)->id == $request->employee_id) return true;
        abort(403);
    }

    private function log(RepairRequest $request, $action, $old, $new, $remarks = null)
    {
        RepairLog::create([
            'repair_request_id' => $request->id,
            'action_by' => Auth::id(),
            'action' => $action,
            'old_status' => $old,
            'new_status' => $new,
            'remarks' => $remarks,
        ]);
    }


public function saveEstimateAndForward(Request $request, $id)
{
    $user = \Auth::user();
    $employee = $user ? $user->employee : null;

    if (!$user || (!$user->hasRole('superuser') && !$user->hasRole('storekeeper'))) {
        abort(403, 'Only Storekeeper can save estimate and forward for verification.');
    }

    $repairRequest = RepairRequest::findOrFail($id);

    /*
     * Storekeeper can manage only own department unless Superuser.
     * Keep this backend check even if UI hides fields.
     */
    if (!$user->hasRole('superuser')) {
        if (!$employee || (int)$repairRequest->department_id !== (int)$employee->department_id) {
            abort(403, 'You can process requests of your own department only.');
        }
    }

    /*
     * Prevent duplicate forward/history if already sent to Programmer/Store Incharge.
     */
    if (in_array($repairRequest->current_handler_role, array('programmer', 'store_incharge'))) {
        return redirect()
            ->route('repair-requests.show', $repairRequest->id)
            ->with('error', 'This request is already sent for verification.');
    }

    $request->validate(array(
        'vendor_id' => 'required|integer',
        'estimated_amount' => 'required|numeric|min:0',
        'estimate_pdf' => 'required|file|mimes:pdf|max:5120',
        'verification_role' => 'required|in:programmer,store_incharge',
        'assigned_to_employee_id' => 'nullable|integer',
        'remarks' => 'nullable|string|max:1000',
    ));

    \DB::beginTransaction();

    try {
        $oldStatus = $repairRequest->status ?: '-';

        /*
         * Upload PDF.
         */
        $pdfPath = null;
        if ($request->hasFile('estimate_pdf')) {
            $pdfPath = $request->file('estimate_pdf')->store('repair-estimates', 'public');
        }

        /*
         * Create estimate.
         * Replace RepairEstimate with your actual estimate model if different.
         */
        $estimate = new RepairEstimate();
        $estimate->repair_request_id = $repairRequest->id;
        $estimate->vendor_id = $request->vendor_id;
        $estimate->estimated_amount = $request->estimated_amount;
        $estimate->estimate_pdf = $pdfPath;
        $estimate->remarks = $request->remarks;
        $estimate->created_by = $user->id;
        $estimate->save();

        /*
         * Save estimate + forward in one request update.
         */
        $newStatus = $request->verification_role === 'store_incharge'
            ? 'Sent to Store Incharge for Verification'
            : 'Sent to Programmer for Verification';

        $repairRequest->selected_estimate_id = $estimate->id;
        $repairRequest->status = $newStatus;
        $repairRequest->current_handler_role = $request->verification_role;

        if ($request->filled('assigned_to_employee_id')) {
            $repairRequest->assigned_to_employee_id = $request->assigned_to_employee_id;
        }

        $repairRequest->save();

        /*
         * Create ONLY ONE history entry for this complete action.
         * If your project has a different history helper, replace this part
         * with that helper, but keep it as one entry only.
         */
        if (method_exists($this, 'addHistory')) {
            $this->addHistory(
                $repairRequest,
                'Storekeeper Action',
                $oldStatus,
                $newStatus,
                'Vendor estimate saved and forwarded for physical/technical verification. '.$request->remarks
            );
        } elseif (class_exists('App\\RepairRequestHistory')) {
            $history = new \App\RepairRequestHistory();
            $history->repair_request_id = $repairRequest->id;
            $history->action_type = 'Storekeeper Action';
            $history->from_status = $oldStatus;
            $history->to_status = $newStatus;
            $history->remarks = 'Vendor estimate saved and forwarded for physical/technical verification. '.$request->remarks;
            $history->created_by = $user->id;
            $history->save();
        }

        \DB::commit();

        return redirect()
            ->route('repair-requests.show', $repairRequest->id)
            ->with('success', 'Vendor estimate saved and sent for verification.');

    } catch (\Exception $e) {
        \DB::rollBack();

        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Estimate could not be saved/forwarded: '.$e->getMessage());
    }
}

public function verifyAndReturnToStorekeeper(Request $request, $id)
{
    $user = \Auth::user();
    $employee = $user ? $user->employee : null;
    $repairRequest = RepairRequest::findOrFail($id);

    $handler = $repairRequest->current_handler_role;

    if (!in_array($handler, array('programmer', 'store_incharge'))) {
        abort(403, 'This request is not pending with Programmer / Store Incharge.');
    }

    $assignedToMe = $employee && $repairRequest->assigned_to_employee_id && (int)$repairRequest->assigned_to_employee_id === (int)$employee->id;
    $isProgrammer = $handler === 'programmer' && $user->hasAnyRole(array('superuser', 'programmer'));
    $isStoreIncharge = $handler === 'store_incharge' && (
        $user->hasRole('superuser')
        || $user->hasRole('store_incharge')
        || ($employee && method_exists($employee, 'hasActiveCharge') && $employee->hasActiveCharge('Store Incharge'))
    );

    if (!$assignedToMe && !$isProgrammer && !$isStoreIncharge) {
        abort(403, 'Only the assigned Programmer / Store Incharge can verify this request.');
    }

    $request->validate(array(
        'verification_decision' => 'required|in:ok,not_ok,revised',
        'verification_remarks' => 'required|string|max:1000',
    ));

    \DB::beginTransaction();

    try {
        $oldStatus = $repairRequest->status ?: '-';
        $verifierLabel = $handler === 'store_incharge' ? 'Store Incharge' : 'Programmer';

        if ($request->verification_decision === 'ok') {
            $newStatus = $verifierLabel.' Verified Estimate OK';
        } elseif ($request->verification_decision === 'not_ok') {
            $newStatus = $verifierLabel.' Estimate Not OK';
        } else {
            $newStatus = $verifierLabel.' Requested Revised Estimate';
        }

        $repairRequest->status = $newStatus;
        $repairRequest->current_handler_role = 'storekeeper';
        $repairRequest->assigned_to_employee_id = null;

        if (\Schema::hasColumn('repair_requests', 'verification_remarks')) {
            $repairRequest->verification_remarks = $request->verification_remarks;
        }

        if (\Schema::hasColumn('repair_requests', 'verified_by')) {
            $repairRequest->verified_by = $user->id;
        }

        if (\Schema::hasColumn('repair_requests', 'verified_at')) {
            $repairRequest->verified_at = now();
        }

        $repairRequest->save();

        $historyRemarks = $verifierLabel.' verified the estimate and returned the request to Storekeeper. '.$request->verification_remarks;

        if (method_exists($this, 'addHistory')) {
            $this->addHistory(
                $repairRequest,
                $verifierLabel.' Verification Remarks',
                $oldStatus,
                $newStatus,
                $historyRemarks
            );
        } elseif (class_exists('App\\RepairRequestHistory')) {
            $history = new \App\RepairRequestHistory();
            $history->repair_request_id = $repairRequest->id;
            $history->action_type = $verifierLabel.' Verification Remarks';
            $history->from_status = $oldStatus;
            $history->to_status = $newStatus;
            $history->remarks = $historyRemarks;
            $history->created_by = $user->id;
            $history->save();
        }

        \DB::commit();

        return redirect()
            ->route('repair-requests.show', $repairRequest->id)
            ->with('success', $verifierLabel.' verification saved and request returned to Storekeeper.');

    } catch (\Exception $e) {
        \DB::rollBack();

        return redirect()
            ->back()
            ->withInput()
            ->with('error', 'Verification could not be saved: '.$e->getMessage());
    }
}

}
