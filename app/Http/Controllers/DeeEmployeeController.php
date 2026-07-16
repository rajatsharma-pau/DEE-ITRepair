<?php
namespace App\Http\Controllers;
use App\Models\DeeEmployee;
use App\Models\DeeEmployeePromotion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class DeeEmployeeController extends Controller
{
    public function index(){ $employees = DeeEmployee::latest()->paginate(25); return view('admin.employees.index', compact('employees')); }
    public function create(){ return view('admin.employees.create'); }
    public function store(Request $request)
    {
        $request->validate([
            'name'=>'required|max:150','phone'=>'required|digits:10|unique:dee_employees,phone','password'=>'required|min:6','role'=>'required',
            'dob'=>'nullable|date','doj'=>'nullable|date','last_promotion_date'=>'nullable|date'
        ]);
        $data = $request->only(['name','phone','email','dob','designation','section','doj','last_promotion_date','room_no','role','status']);
        $data['password'] = Hash::make($request->password);
        $data['retirement_date'] = $request->dob ? Carbon::parse($request->dob)->addYears(60)->endOfMonth()->toDateString() : null;
        DeeEmployee::create($data);
        return redirect()->route('dee.employees.index')->with('success','Employee created.');
    }
    public function edit($id){ $employee = DeeEmployee::with('promotions')->findOrFail($id); return view('admin.employees.edit', compact('employee')); }
    public function update(Request $request, $id)
    {
        $employee = DeeEmployee::findOrFail($id);
        $request->validate(['name'=>'required|max:150','phone'=>'required|digits:10|unique:dee_employees,phone,'.$id,'dob'=>'nullable|date']);
        $data = $request->only(['name','phone','email','dob','designation','section','doj','last_promotion_date','room_no','role','status']);
        if ($request->filled('password')) $data['password'] = Hash::make($request->password);
        $data['retirement_date'] = $request->dob ? Carbon::parse($request->dob)->addYears(60)->endOfMonth()->toDateString() : null;
        $employee->update($data);
        return redirect()->route('dee.employees.index')->with('success','Employee updated.');
    }
    public function destroy($id){ DeeEmployee::findOrFail($id)->delete(); return back()->with('success','Employee deleted.'); }
    public function addPromotion(Request $request, $id)
    {
        $request->validate(['new_designation'=>'required','promotion_date'=>'required|date|before_or_equal:today']);
        $emp = DeeEmployee::findOrFail($id);
        DeeEmployeePromotion::create([
            'employee_id'=>$id,'old_designation'=>$emp->designation,'new_designation'=>$request->new_designation,
            'promotion_date'=>$request->promotion_date,'remarks'=>$request->remarks
        ]);
        $emp->designation = $request->new_designation;
        $emp->last_promotion_date = $request->promotion_date;
        $emp->save();
        return back()->with('success','Promotion added.');
    }
}
