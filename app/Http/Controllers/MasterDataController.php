<?php

namespace App\Http\Controllers;

use App\College;
use App\Department;
use App\Designation;
use App\Employee;
use App\ProblemTemplate;
use App\RepairCategory;
use App\RepairRoutingRule;
use App\Section;
use App\Vendor;
use Illuminate\Http\Request;
use App\Support\AccessScope;

class MasterDataController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:admin,college_admin,department_admin,director']);
    }


    public function colleges()
    {
        $items = AccessScope::colleges();
        return view('masters.colleges', compact('items'));
    }

    public function storeCollege(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:100',
            'is_active' => 'nullable',
        ]);
        $data['is_active'] = $request->has('is_active');
        College::create($data);
        return back()->with('success','College / Directorate added.');
    }

    public function departments()
    {
        // Departments table has id and college_id. It does not have department_id.
        // Therefore, pass department column as 'id' while applying department-level scope.
        $items = AccessScope::apply(Department::with('college'), 'college_id', 'id')
            ->orderBy('name')
            ->get();

        $colleges = AccessScope::colleges();
        return view('masters.departments', compact('items','colleges'));
    }

    public function storeDepartment(Request $request)
    {
        $data = $request->validate([
            'college_id' => 'required|exists:colleges,id',
            'name' => 'required|string|max:255',
            'place' => 'nullable|string|max:255',
            'is_active' => 'nullable',
        ]);
        $data['is_active'] = $request->has('is_active');
        Department::create($data);
        return back()->with('success','Department / Office / KVK added.');
    }

    public function designations()
    {
        $items = Designation::orderBy('sort_order')->orderBy('name')->get();
        return view('masters.designations', compact('items'));
    }

    public function storeDesignation(Request $request)
    {
        $data = $request->validate(['name'=>'required|string|max:255','cadre'=>'nullable|string|max:255','sort_order'=>'nullable|integer','is_active'=>'nullable']);
        $data['is_active'] = $request->has('is_active');
        Designation::create($data);
        return back()->with('success','Designation added.');
    }

    public function sections()
    {
        $items = Section::with(['college','department'])->orderBy('name')->get();
        $colleges = College::where('is_active', 1)->orderBy('name')->get();
        $departments = Department::where('is_active', 1)->orderBy('name')->get();
        return view('masters.sections', compact('items','colleges','departments'));
    }

    public function storeSection(Request $request)
    {
        $data = $request->validate([
            'college_id' => 'nullable|exists:colleges,id',
            'department_id' => 'nullable|exists:departments,id',
            'name' => 'required|string|max:255',
            'short_name' => 'nullable|string|max:50',
            'is_active' => 'nullable'
        ]);

        if (!empty($data['department_id'])) {
            $dept = Department::find($data['department_id']);
            if ($dept) {
                $data['college_id'] = $dept->college_id;
            }
        }

        $data['is_active'] = $request->has('is_active');
        Section::create($data);
        return back()->with('success','Section added.');
    }

    public function vendors()
    {
        $items = Vendor::orderBy('name')->get();
        return view('masters.vendors', compact('items'));
    }

    public function storeVendor(Request $request)
    {
        $data = $request->validate([
            'name'=>'required|string|max:255',
            'contact_person'=>'nullable|string|max:255',
            'mobile'=>'nullable|string|max:20',
            'email'=>'nullable|email|max:255',
            'address'=>'nullable|string',
            'gst_no'=>'nullable|string|max:50',
            'pan_no'=>'nullable|string|max:50',
            'vendor_type'=>'required|in:Computer,Electrical,Furniture,General,Other',
            'is_active'=>'nullable',
        ]);
        $data['is_active'] = $request->has('is_active');
        Vendor::create($data);
        return back()->with('success','Vendor added.');
    }

    public function repairCategories()
    {
        $items = RepairCategory::orderBy('item_group')->orderBy('name')->get();
        return view('masters.repair_categories', compact('items'));
    }

    public function storeRepairCategory(Request $request)
    {
        $data = $request->validate(['name'=>'required|string|max:255','item_group'=>'required|in:Computer Related,Non Computer,General','default_handler'=>'required|in:programmer,storekeeper,store_incharge,director','is_active'=>'nullable']);
        $data['is_active'] = $request->has('is_active');
        RepairCategory::create($data);
        return back()->with('success','Repair category added.');
    }



    public function problemTemplates()
    {
        $items = ProblemTemplate::with('category')->orderBy('title')->get();
        $categories = RepairCategory::where('is_active',1)->orderBy('item_group')->orderBy('name')->get();
        return view('masters.problem_templates', compact('items','categories'));
    }

    public function storeProblemTemplate(Request $request)
    {
        $data = $request->validate([
            'repair_category_id'=>'nullable|exists:repair_categories,id',
            'title'=>'required|string|max:255',
            'description'=>'nullable|string',
            'item_group'=>'nullable|in:Computer Related,Non Computer,General',
            'is_active'=>'nullable',
        ]);
        $data['is_active'] = $request->has('is_active');
        if (!empty($data['repair_category_id'])) {
            $category = RepairCategory::find($data['repair_category_id']);
            $data['item_group'] = $data['item_group'] ?: optional($category)->item_group;
        }
        ProblemTemplate::create($data);
        return back()->with('success','Default problem / material requirement added.');
    }

    public function routingRules()
    {
        $items = RepairRoutingRule::with(['category','handlerEmployee'])->latest()->get();
        $categories = RepairCategory::where('is_active',1)->orderBy('name')->get();
        $employees = AccessScope::employeesQuery()->orderBy('first_name')->get();
        return view('masters.routing_rules', compact('items','categories','employees'));
    }

    public function storeRoutingRule(Request $request)
    {
        $data = $request->validate([
            'repair_category_id'=>'required|exists:repair_categories,id',
            'handler_type'=>'required|in:role,charge,employee',
            'handler_value'=>'nullable|string|max:255',
            'handler_employee_id'=>'nullable|exists:employees,id',
            'requires_store_verification'=>'nullable',
            'requires_store_incharge_approval'=>'nullable',
            'requires_programmer_verification'=>'nullable',
            'is_active'=>'nullable',
        ]);
        $data['requires_store_verification'] = $request->has('requires_store_verification');
        $data['requires_store_incharge_approval'] = $request->has('requires_store_incharge_approval');
        $data['requires_programmer_verification'] = $request->has('requires_programmer_verification');
        $data['is_active'] = $request->has('is_active');
        RepairRoutingRule::create($data);
        return back()->with('success','Routing rule added.');
    }
}
