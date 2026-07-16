<?php
namespace App\Http\Controllers;
use App\Models\DeeRepairRequest;
use App\Models\DeeRepairLog;
use App\Models\DeeEmployee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class DeeRepairRequestController extends Controller
{
    private function log($requestId, $action, $old, $new, $remarks=null)
    {
        DeeRepairLog::create([
            'repair_request_id'=>$requestId,
            'action_by'=>Auth::guard('dee')->id(),
            'action'=>$action,
            'old_status'=>$old,
            'new_status'=>$new,
            'remarks'=>$remarks
        ]);
    }

    public function index()
    {
        $user = Auth::guard('dee')->user();
        $query = DeeRepairRequest::with('employee','programmer','storekeeper')->latest();
        if ($user->role == 'employee') $query->where('employee_id',$user->id);
        if ($user->role == 'storekeeper') $query->whereIn('status',['submitted','verified_by_store','forwarded_to_programmer','need_part','sent_outside']);
        if ($user->role == 'programmer') $query->whereIn('status',['forwarded_to_programmer','in_progress','need_part','reopened']);
        $requests = $query->paginate(25);
        return view('repair_requests.index', compact('requests','user'));
    }

    public function create(){ return view('repair_requests.create'); }

    public function store(Request $request)
    {
        $request->validate([
            'item_type'=>'required','problem_description'=>'required|min:10','priority'=>'required|in:normal,urgent',
            'attachment'=>'nullable|image|mimes:jpg,jpeg,png|max:1024'
        ]);
        $path = null;
        if ($request->hasFile('attachment')) $path = $request->file('attachment')->store('dee_repair_attachments','public');
        $requestNo = 'DEE-REP-'.date('Y').'-'.str_pad((DeeRepairRequest::whereYear('created_at',date('Y'))->count()+1),4,'0',STR_PAD_LEFT);
        $repair = DeeRepairRequest::create([
            'request_no'=>$requestNo,
            'employee_id'=>Auth::guard('dee')->id(),
            'item_type'=>$request->item_type,
            'item_name'=>$request->item_name,
            'inventory_no'=>$request->inventory_no,
            'problem_description'=>$request->problem_description,
            'room_no'=>$request->room_no,
            'priority'=>$request->priority,
            'attachment'=>$path,
            'status'=>'submitted'
        ]);
        $this->log($repair->id,'Request submitted',null,'submitted','Employee submitted repair request.');
        return redirect()->route('dee.repairs.index')->with('success','Repair request submitted. Request No: '.$requestNo);
    }

    public function show($id)
    {
        $repair = DeeRepairRequest::with('employee','logs.actor')->findOrFail($id);
        $user = Auth::guard('dee')->user();
        if ($user->role == 'employee' && $repair->employee_id != $user->id) abort(403);
        $programmers = DeeEmployee::where('role','programmer')->where('status','active')->get();
        return view('repair_requests.show', compact('repair','user','programmers'));
    }

    public function storekeeperVerify(Request $request, $id)
    {
        $request->validate(['storekeeper_remarks'=>'required|min:5','programmer_id'=>'required|exists:dee_employees,id']);
        $repair = DeeRepairRequest::findOrFail($id);
        $old = $repair->status;
        $repair->update([
            'status'=>'forwarded_to_programmer',
            'storekeeper_id'=>Auth::guard('dee')->id(),
            'programmer_id'=>$request->programmer_id,
            'storekeeper_remarks'=>$request->storekeeper_remarks
        ]);
        $this->log($repair->id,'Verified and forwarded by storekeeper',$old,'forwarded_to_programmer',$request->storekeeper_remarks);
        return back()->with('success','Request forwarded to programmer.');
    }

    public function programmerUpdate(Request $request, $id)
    {
        $request->validate(['status'=>'required|in:in_progress,need_part,sent_outside,not_repairable,repaired','programmer_remarks'=>'required|min:5']);
        $repair = DeeRepairRequest::findOrFail($id);
        $old = $repair->status;
        $repair->update(['status'=>$request->status,'programmer_id'=>Auth::guard('dee')->id(),'programmer_remarks'=>$request->programmer_remarks]);
        $this->log($repair->id,'Updated by programmer',$old,$request->status,$request->programmer_remarks);
        return back()->with('success','Request updated.');
    }

    public function employeeClose(Request $request, $id)
    {
        $request->validate(['employee_feedback'=>'required|min:3','final_action'=>'required|in:close,reopen']);
        $repair = DeeRepairRequest::findOrFail($id);
        if ($repair->employee_id != Auth::guard('dee')->id()) abort(403);
        $old = $repair->status;
        $new = $request->final_action == 'close' ? 'closed' : 'reopened';
        $repair->update(['status'=>$new,'employee_feedback'=>$request->employee_feedback,'closed_at'=>$new=='closed'?Carbon::now():null]);
        $this->log($repair->id, $new=='closed'?'Closed by employee':'Reopened by employee', $old, $new, $request->employee_feedback);
        return back()->with('success','Request '.$new.'.');
    }
}
