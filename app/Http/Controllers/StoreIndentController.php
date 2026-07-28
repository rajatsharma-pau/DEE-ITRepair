<?php

namespace App\Http\Controllers;

use App\Employee;
use App\StoreIndent;
use App\StoreIndentItem;
use App\StoreItem;
use App\StoreStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Support\AccessScope;

class StoreIndentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $employee = $user->employee;
        $baseQuery = StoreIndent::with('employee','issuedBy','items.storeItem');
        if (!AccessScope::isEmployeeOnly($user)) AccessScope::apply($baseQuery);
        if (request()->filled('status')) {
            $baseQuery->where('status', request('status'));
        }

        $indents = $baseQuery
            ->when(AccessScope::isEmployeeOnly($user) && $employee, function($q) use ($employee) {
                $q->where('employee_id', $employee->id);
            })
            ->latest()->paginate(20);
        return view('store_indents.index', compact('indents'));
    }

  
        public function create()
{
    $departmentId = AccessScope::departmentId();

    $items = StoreItem::where('is_active', 1)
        ->where('department_id', $departmentId)
        ->orderBy('name')
        ->get();

    $requiredDate = date('Y-m-d');

    return view('store_indents.create', compact('items', 'requiredDate'));
}
      

    public function store(Request $request)
    {
        $employee = Auth::user()->employee;
        if (!$employee) return back()->withErrors('Employee profile not found.');
        $data = $request->validate([
            'required_date' => 'required|date',
            'employee_remarks' => 'nullable|string',
            'store_item_id' => 'required|array',
            'store_item_id.*' => 'required|exists:store_items,id',
            'requested_qty' => 'required|array',
            'requested_qty.*' => 'required|numeric|min:0.01',
        ]);

        DB::transaction(function() use ($data, $employee) {
            $indent = StoreIndent::create([
                'indent_no' => $this->generateIndentNo(),
                'employee_id' => $employee->id,
                'college_id' => $employee->college_id,
                'department_id' => $employee->department_id,
                'status' => 'Submitted',
                'required_date' => !empty($data['required_date']) ? $data['required_date'] : date('Y-m-d'),
                'employee_remarks' => $data['employee_remarks'],
            ]);
            foreach ($data['store_item_id'] as $i => $itemId) {
                if (!$itemId || empty($data['requested_qty'][$i])) continue;
                StoreIndentItem::create([
                    'store_indent_id' => $indent->id,
                    'store_item_id' => $itemId,
                    'requested_qty' => $data['requested_qty'][$i],
                    'approved_qty' => $data['requested_qty'][$i],
                ]);
            }
        });
        return redirect()->route('store-indents.index')->with('success','Indent submitted to Storekeeper.');
    }

    public function show(StoreIndent $store_indent)
    {
        $this->authorizeView($store_indent);
        $store_indent->load('employee','issuedBy','items.storeItem','stockMovements.storeItem');
        return view('store_indents.show', ['indent' => $store_indent]);
    }

    public function issue(Request $request, StoreIndent $store_indent)
    {
        if (!Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper'])) abort(403);
        if (!AccessScope::canAccessDepartment($store_indent->department_id)) abort(403);
        $data = $request->validate([
            'issued_qty' => 'required|array',
            'issued_qty.*' => 'nullable|numeric|min:0',
            'storekeeper_remarks' => 'nullable|string',
        ]);

        $storekeeperEmployee = Auth::user()->employee;
        try {
            DB::transaction(function() use ($store_indent, $data, $storekeeperEmployee) {
                $totalRequested = 0; $totalIssued = 0;
                foreach ($store_indent->items as $line) {
                    $qty = isset($data['issued_qty'][$line->id]) ? floatval($data['issued_qty'][$line->id]) : 0;
                    if ($qty <= 0) continue;
                    $item = StoreItem::lockForUpdate()->find($line->store_item_id);
                    if ($item->current_stock < $qty) {
                        throw new \Exception('Insufficient stock for '.$item->name.'. Available: '.$item->current_stock.' '.$item->unit);
                    }
                    $item->current_stock = $item->current_stock - $qty;
                    $item->save();
                    $line->issued_qty = $qty;
                    $line->approved_qty = $qty;
                    $line->save();
                    $totalIssued += $qty;
                    StoreStockMovement::create([
                        'store_item_id' => $item->id,
                        'store_indent_id' => $store_indent->id,
                        'employee_id' => $store_indent->employee_id,
                        'created_by' => Auth::id(),
                        'movement_type' => 'Issue',
                        'quantity' => -1 * $qty,
                        'balance_after' => $item->current_stock,
                        'movement_date' => date('Y-m-d'),
                        'remarks' => 'Issued against indent '.$store_indent->indent_no,
                    ]);
                }
                foreach ($store_indent->items as $line) $totalRequested += $line->requested_qty;
                $store_indent->issued_by_employee_id = optional($storekeeperEmployee)->id;
                $store_indent->issued_date = date('Y-m-d');
                $store_indent->storekeeper_remarks = $data['storekeeper_remarks'];
                $store_indent->status = $totalIssued >= $totalRequested ? 'Issued' : 'Partially Issued';
                $store_indent->save();
            });
        } catch (\Exception $e) {
            return back()->withErrors($e->getMessage());
        }
        return back()->with('success','Items issued and stock decremented.');
    }

    public function reject(Request $request, StoreIndent $store_indent)
    {
        if (!Auth::user()->isRole(['admin','college_admin','department_admin','director','storekeeper'])) abort(403);
        if (!AccessScope::canAccessDepartment($store_indent->department_id)) abort(403);
        $data = $request->validate(['storekeeper_remarks' => 'required|string']);
        $store_indent->status = 'Rejected';
        $store_indent->storekeeper_remarks = $data['storekeeper_remarks'];
        $store_indent->save();
        return back()->with('success','Indent rejected.');
    }

    private function generateIndentNo()
    {
        $year = date('Y');
        $count = StoreIndent::whereYear('created_at', $year)->count() + 1;
        return 'DEE-IND-'.$year.'-'.str_pad($count, 4, '0', STR_PAD_LEFT);
    }

    private function authorizeView(StoreIndent $indent)
    {
        $user = Auth::user();
        if ($user->isRole(['admin','college_admin','department_admin','director','storekeeper']) && AccessScope::canAccessDepartment($indent->department_id, $user)) return true;
        if ($user->employee && $indent->employee_id == $user->employee->id) return true;
        abort(403);
    }
}
