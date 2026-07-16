<?php
namespace App\Http\Controllers;
use App\RepairRequest; use App\RepairLog; use Illuminate\Http\Request; use Illuminate\Support\Facades\Auth;
class ProgrammerController extends Controller
{
    public function index(){ $requests=RepairRequest::with(['employee','asset'])->where(function($q){ $q->where('programmer_id',Auth::id())->orWhereNull('programmer_id'); })->whereIn('status',['Forwarded to Programmer','In Progress','Need Part','Sent Outside'])->latest()->paginate(20); return view('programmer.index', compact('requests')); }
    public function show(RepairRequest $request){ $request->load(['employee','asset','storekeeper']); return view('programmer.show',['repair_request'=>$request]); }
    public function updateStatus(Request $request, RepairRequest $repair_request){
        $request->validate(['status'=>'required','programmer_remarks'=>'required']); $old=$repair_request->status;
        $repair_request->update(['status'=>$request->status,'programmer_id'=>Auth::id(),'programmer_remarks'=>$request->programmer_remarks,'closed_at'=>in_array($request->status,['Repaired','Not Repairable','Closed'])?now():null]);
        RepairLog::create(['repair_request_id'=>$repair_request->id,'action_by'=>Auth::id(),'action'=>'Programmer updated request','old_status'=>$old,'new_status'=>$request->status,'remarks'=>$request->programmer_remarks]);
        return back()->with('success','Request updated.');
    }
}
