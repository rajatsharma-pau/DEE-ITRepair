<?php

namespace App\Http\Controllers;

use App\College;
use App\Department;
use App\StoreItem;
use App\StoreStockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Support\AccessScope;

class StoreItemController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin,college_admin,department_admin,director,storekeeper')->except(['index','show']);
    }

    public function index(Request $request)
    {
        $query = AccessScope::apply(StoreItem::query());
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($x) use ($q) {
                $x->where('name','like',"%$q%")
                  ->orWhere('item_code','like',"%$q%")
                  ->orWhere('category','like',"%$q%")
                  ->orWhere('brand','like',"%$q%");
            });
        }
        if ($request->filled('college_id')) $query->where('college_id', $request->college_id);
        if ($request->filled('department_id')) $query->where('department_id', $request->department_id);
        if ($request->filled('low_stock')) $query->whereRaw('current_stock <= reorder_level');
        $items = $query->orderBy('name')->paginate(20);
        $colleges = AccessScope::colleges();
        $departments = AccessScope::departments();
        return view('store_items.index', compact('items','colleges','departments')); 
    }

    public function create()
    {
        $item = new StoreItem(['unit' => 'Nos', 'is_active' => 1]);
        $colleges = AccessScope::colleges();
        $departments = AccessScope::departments();
        return view('store_items.create', compact('item','colleges','departments'));
    }

    public function store(Request $request)
    {
        $data = $this->validateItem($request);
        $this->authorizeScopeFields($data);
        $data['current_stock'] = $data['opening_stock'];
        $item = StoreItem::create($data);
        StoreStockMovement::create([
            'store_item_id' => $item->id,
            'movement_type' => 'Opening',
            'quantity' => $item->opening_stock,
            'balance_after' => $item->current_stock,
            'movement_date' => date('Y-m-d'),
            'created_by' => Auth::id(),
            'remarks' => 'Opening stock entry',
        ]);
        return redirect()->route('store-items.show', $item)->with('success','Store item created.');
    }

    public function show(StoreItem $store_item)
    {
        $this->authorizeItemScope($store_item);
        $store_item->load('stockMovements.employee','stockMovements.createdBy');
        return view('store_items.show', ['item' => $store_item]);
    }

    public function edit(StoreItem $store_item)
    {
        $this->authorizeItemScope($store_item);
        $colleges = AccessScope::colleges();
        $departments = AccessScope::departments();
        return view('store_items.edit', ['item' => $store_item, 'colleges' => $colleges, 'departments' => $departments]);
    }

    public function update(Request $request, StoreItem $store_item)
    {
        $this->authorizeItemScope($store_item);
        $data = $this->validateItem($request, $store_item->id);
        $this->authorizeScopeFields($data);
        unset($data['opening_stock']);
        $store_item->update($data);
        return redirect()->route('store-items.show', $store_item)->with('success','Store item updated.');
    }

    public function adjustStock(Request $request, StoreItem $store_item)
    {
        $this->authorizeItemScope($store_item);
        $data = $request->validate([
            'movement_type' => 'required|in:Stock In,Return,Adjustment',
            'quantity' => 'required|numeric',
            'remarks' => 'required|string',
        ]);
        $qty = $data['quantity'];
        if ($data['movement_type'] == 'Adjustment') {
            $store_item->current_stock = $qty;
        } else {
            $store_item->current_stock = $store_item->current_stock + $qty;
        }
        if ($store_item->current_stock < 0) return back()->withErrors('Stock cannot be negative.');
        $store_item->save();
        StoreStockMovement::create([
            'store_item_id' => $store_item->id,
            'movement_type' => $data['movement_type'],
            'quantity' => $qty,
            'balance_after' => $store_item->current_stock,
            'movement_date' => date('Y-m-d'),
            'created_by' => Auth::id(),
            'remarks' => $data['remarks'],
        ]);
        return back()->with('success','Stock updated successfully.');
    }

    private function authorizeItemScope(StoreItem $item)
    {
        if (!AccessScope::canAccessDepartment($item->department_id)) abort(403);
    }

    private function authorizeScopeFields(array $data)
    {
        if (!empty($data['department_id']) && !AccessScope::canAccessDepartment($data['department_id'])) abort(403);
        if (!empty($data['college_id']) && !AccessScope::canAccessCollege($data['college_id'])) abort(403);
    }

    private function validateItem(Request $request, $id = null)
    {
        return $request->validate([
            'college_id' => 'nullable|exists:colleges,id',
            'department_id' => 'nullable|exists:departments,id',
            'item_code' => 'nullable|string|max:100|unique:store_items,item_code'.($id ? ','.$id : ''),
            'name' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'brand' => 'nullable|string|max:255',
            'unit' => 'required|string|max:50',
            'opening_stock' => 'nullable|numeric|min:0',
            'reorder_level' => 'nullable|numeric|min:0',
            'location' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'is_active' => 'nullable|boolean',
        ]);
    }
}
