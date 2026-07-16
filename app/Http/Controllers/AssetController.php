<?php

namespace App\Http\Controllers;

use App\Asset;
use App\AssetHistory;
use App\College;
use App\Department;
use App\Directorate;
use App\Employee;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\AccessScope;

class AssetController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin,college_admin,department_admin,director,storekeeper')->except(['index','show','assetJson','byEmployee']);
    }

    public function index(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        $query = Asset::with(['assignedTo','college','department']);

        if (!\App\Support\AccessScope::isEmployeeOnly($user)) {
            AccessScope::apply($query);
        }

        // Employee can view only assets currently allocated to him/her.
        if (\App\Support\AccessScope::isEmployeeOnly($user)) {
            if (!$employee) {
                $query->whereRaw('1 = 0');
            } else {
                $query->where('assigned_to_employee_id', $employee->id)
                      ->where('asset_state', 'With Employee');
            }
        }

        // Admin / Director / Storekeeper can see assets of any individual employee.
        if ($user->isRole(['admin','college_admin','department_admin','director','storekeeper']) && $request->filled('employee_id')) {
            $query->where('assigned_to_employee_id', $request->employee_id);
        }

        if ($request->filled('college_id')) $query->where('college_id', $request->college_id);
        if ($request->filled('department_id')) $query->where('department_id', $request->department_id);
        if ($request->filled('asset_state')) $query->where('asset_state', $request->asset_state);
        if ($request->filled('asset_category')) $query->where('asset_category', $request->asset_category);
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($x) use ($q) {
                $x->where('item_name','like',"%$q%")
                  ->orWhere('asset_code','like',"%$q%")
                  ->orWhere('inventory_no','like',"%$q%")
                  ->orWhere('make','like',"%$q%")
                  ->orWhere('model','like',"%$q%")
                  ->orWhere('serial_no','like',"%$q%");
            });
        }

        $assets = $query->latest()->paginate(20);
        $categories = $this->categories();
        $states = $this->states();
        $employees = $user->isRole(['admin','college_admin','department_admin','director','storekeeper']) ? AccessScope::employeesQuery()->orderBy('first_name')->get() : collect();
        $colleges = AccessScope::colleges();
        $departments = AccessScope::departments();
        return view('assets.index', compact('assets','categories','states','employees','colleges','departments')); 
    }

    public function create()
    {
        $asset = new Asset(['asset_state' => 'In Store', 'condition_status' => 'Working', 'state_date' => date('Y-m-d')]);
        $employees = AccessScope::employeesQuery()->with(['college','department'])->orderBy('first_name')->get();
        $colleges = AccessScope::colleges();
        $departments = AccessScope::departments();
        $categories = $this->categories();
        $states = $this->states();
        $conditions = $this->conditions();
        return view('assets.create', compact('asset','employees','colleges','departments','categories','states','conditions'));
    }

    public function store(Request $request)
    {
        $data = $this->validateAsset($request);
        $this->authorizeScopeFields($data);
        $data['directorate_id'] = Directorate::where('short_name','DEE')->value('id');
        $data = $this->syncEmployeeAllocationFields($data);
        $data['state_date'] = $data['state_date'] ?: date('Y-m-d');

        $asset = Asset::create($data);
        $this->history($asset, 'Created', null, $asset->asset_state, $asset->assigned_to_employee_id, 'Asset created. '.$asset->remarks);

        return redirect()->route('assets.show', $asset)->with('success', 'Asset added successfully.');
    }

    public function show(Asset $asset)
    {
        $this->authorizeAssetView($asset);
        $asset->load(['assignedTo','histories.employee','histories.actionBy','college','department']);
        $employees = AccessScope::employeesQuery()->orderBy('first_name')->get();
        $states = $this->states();
        return view('assets.show', compact('asset','employees','states'));
    }

    public function edit(Asset $asset)
    {
        $employees = AccessScope::employeesQuery()->with(['college','department'])->orderBy('first_name')->get();
        $colleges = AccessScope::colleges();
        $departments = AccessScope::departments();
        $categories = $this->categories();
        $states = $this->states();
        $conditions = $this->conditions();
        return view('assets.edit', compact('asset','employees','colleges','departments','categories','states','conditions'));
    }

    public function update(Request $request, Asset $asset)
    {
        $this->authorizeAssetView($asset);
        $oldState = $asset->asset_state;
        $oldEmployee = $asset->assigned_to_employee_id;
        $data = $this->validateAsset($request, $asset->id);
        $this->authorizeScopeFields($data);
        $data = $this->syncEmployeeAllocationFields($data);
        $asset->update($data);

        if ($oldState != $asset->asset_state || $oldEmployee != $asset->assigned_to_employee_id) {
            $this->history($asset, 'Status Updated', $oldState, $asset->asset_state, $asset->assigned_to_employee_id, $request->remarks);
        }

        return redirect()->route('assets.show', $asset)->with('success', 'Asset updated successfully.');
    }

    public function addHistory(Request $request, Asset $asset)
    {
        $this->authorizeAssetView($asset);
        $data = $request->validate([
            'asset_state' => 'required|in:'.implode(',', $this->states()),
            'assigned_to_employee_id' => 'nullable|exists:employees,id',
            'action_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);
        $old = $asset->asset_state;
        $asset->asset_state = $data['asset_state'];
        $asset->state_date = $data['action_date'] ?: date('Y-m-d');
        $asset->assigned_to_employee_id = $asset->asset_state == 'With Employee' ? $data['assigned_to_employee_id'] : null;
        if ($asset->assigned_to_employee_id) {
            $emp = Employee::find($asset->assigned_to_employee_id);
            if ($emp) {
                $asset->college_id = $emp->college_id;
                $asset->department_id = $emp->department_id;
                $asset->location = $emp->room_no ?: $asset->location;
            }
        }
        $asset->save();
        $this->history($asset, $data['asset_state'], $old, $asset->asset_state, $asset->assigned_to_employee_id, $data['remarks']);
        return back()->with('success','Asset movement/history saved.');
    }

    // JSON for new repair form. Respects asset visibility.
    public function assetJson(Asset $asset)
    {
        $this->authorizeAssetView($asset);
        $asset->load('assignedTo');
        return response()->json($this->assetPayload($asset));
    }

    // JSON allocated assets by employee. Used when storekeeper/admin selects an employee on request form.
    public function byEmployee(Employee $employee)
    {
        $user = Auth::user();
        if (\App\Support\AccessScope::isEmployeeOnly($user) && optional($user->employee)->id != $employee->id) {
            abort(403);
        }
        if (!$user->isRole(['admin','college_admin','department_admin','director','storekeeper','employee'])) {
            abort(403);
        }

        if (!AccessScope::canAccessEmployee($employee, $user)) abort(403);

        $assets = Asset::where('assigned_to_employee_id', $employee->id)
            ->where('asset_state', 'With Employee')
            ->orderBy('item_name')
            ->get()
            ->map(function($asset){ return $this->assetPayload($asset); });

        return response()->json($assets);
    }

    private function assetPayload(Asset $asset)
    {
        $assigned = $asset->assignedTo;
        return [
            'id' => $asset->id,
            'label' => trim(($asset->inventory_no ?: $asset->asset_code ?: 'No Inventory').' - '.$asset->item_name.' '.($asset->make ? '(' . $asset->make . ' ' . $asset->model . ')' : '')),
            'asset_category' => $asset->asset_category,
            'item_type' => $asset->asset_category,
            'item_name' => trim($asset->item_name.' '.($asset->make ? '(' . $asset->make . ' ' . $asset->model . ')' : '')),
            'inventory_no' => $asset->inventory_no,
            'room_no' => $asset->location ?: optional($assigned)->room_no,
            'assigned_to_employee_id' => $asset->assigned_to_employee_id,
            'suggested_category_name' => $this->suggestedCategoryNameForAsset($asset),
        ];
    }

    private function suggestedCategoryNameForAsset(Asset $asset)
    {
        $map = [
            'Computer' => 'Computer', 'Printer' => 'Printer', 'Scanner' => 'Computer',
            'UPS' => 'UPS / Battery', 'Chair' => 'Furniture', 'Table' => 'Furniture',
            'Sound System' => 'Electrical', 'Speaker' => 'Electrical', 'Webcam' => 'Computer',
            'Projector' => 'Computer', 'Furniture' => 'Furniture', 'Electrical' => 'Electrical',
        ];
        return isset($map[$asset->asset_category]) ? $map[$asset->asset_category] : 'General Repair';
    }

    private function authorizeAssetView(Asset $asset)
    {
        $user = Auth::user();
        if ($user->isRole(['admin','college_admin','department_admin','director','storekeeper']) && AccessScope::canAccessDepartment($asset->department_id, $user)) return true;
        if ($user->hasRole('employee') && optional($user->employee)->id == $asset->assigned_to_employee_id && $asset->asset_state == 'With Employee') return true;
        abort(403, 'You are not allowed to view this asset.');
    }

    private function authorizeScopeFields(array $data)
    {
        if (!empty($data['department_id']) && !AccessScope::canAccessDepartment($data['department_id'])) {
            abort(403, 'Selected department is outside your scope.');
        }
        if (!empty($data['college_id']) && !AccessScope::canAccessCollege($data['college_id'])) {
            abort(403, 'Selected college is outside your scope.');
        }
        if (!empty($data['assigned_to_employee_id'])) {
            $emp = Employee::find($data['assigned_to_employee_id']);
            if ($emp && !AccessScope::canAccessEmployee($emp)) abort(403, 'Selected employee is outside your scope.');
        }
    }

    private function syncEmployeeAllocationFields(array $data)
    {
        if ($data['asset_state'] != 'With Employee') {
            $data['assigned_to_employee_id'] = null;
        }
        if (!empty($data['assigned_to_employee_id'])) {
            $emp = Employee::find($data['assigned_to_employee_id']);
            if ($emp) {
                $data['college_id'] = $emp->college_id;
                $data['department_id'] = $emp->department_id;
                if (empty($data['location'])) $data['location'] = $emp->room_no;
            }
        }
        return $data;
    }

    private function validateAsset(Request $request, $id = null)
    {
        return $request->validate([
            'college_id' => 'nullable|exists:colleges,id',
            'department_id' => 'nullable|exists:departments,id',
            'asset_code' => 'nullable|string|max:100|unique:assets,asset_code'.($id ? ','.$id : ''),
            'inventory_no' => 'nullable|string|max:100|unique:assets,inventory_no'.($id ? ','.$id : ''),
            'asset_category' => 'required|in:'.implode(',', $this->categories()),
            'item_name' => 'required|string|max:255',
            'make' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:255',
            'serial_no' => 'nullable|string|max:255',
            'configuration' => 'nullable|string|max:255',
            'location' => 'nullable|string|max:255',
            'purchase_date' => 'nullable|date',
            'purchase_amount' => 'nullable|numeric|min:0',
            'purchase_order_no' => 'nullable|string|max:255',
            'warranty_till' => 'nullable|date',
            'condition_status' => 'required|in:'.implode(',', $this->conditions()),
            'asset_state' => 'required|in:'.implode(',', $this->states()),
            'assigned_to_employee_id' => 'nullable|exists:employees,id',
            'state_date' => 'nullable|date',
            'remarks' => 'nullable|string',
        ]);
    }

    private function history(Asset $asset, $action, $from, $to, $employeeId, $remarks)
    {
        AssetHistory::create([
            'asset_id' => $asset->id,
            'employee_id' => $employeeId,
            'action_by' => Auth::id(),
            'action_type' => in_array($action, ['Assigned','Returned to Store','Sent for Repair','Repair Completed','Sent for Auction','Scrap/Auctioned','Lost','Created']) ? $action : 'Status Updated',
            'from_state' => $from,
            'to_state' => $to,
            'action_date' => Carbon::now()->toDateString(),
            'remarks' => $remarks,
        ]);
    }

    private function categories(){ return ['Computer','Printer','Scanner','UPS','Chair','Table','Sound System','Speaker','Webcam','Projector','Furniture','Electrical','Other']; }
    private function states(){ return ['In Store','With Employee','Under Repair','Returned to Store','Sent for Auction','Scrap/Auctioned','Lost']; }
    private function conditions(){ return ['Working','Needs Repair','Not Working','Obsolete','Condemned']; }
}
