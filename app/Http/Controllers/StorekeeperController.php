<?php
namespace App\Http\Controllers;
use App\RepairRequest; use App\RepairLog; use App\User; use Illuminate\Http\Request; use Illuminate\Support\Facades\Auth;
class StorekeeperController extends Controller
{
    public function index(){ $requests=RepairRequest::with(['employee','asset'])->whereIn('status',['Submitted','Reopened'])->latest()->paginate(20); return view('storekeeper.index', compact('requests')); }
    public function show(RepairRequest $request){ $request->load(['employee','asset']); $programmers=User::where('role','programmer')->where('is_active',1)->get(); return view('storekeeper.show',['repair_request'=>$request,'programmers'=>$programmers]); }
    public function forward(Request $request, RepairRequest $repair_request){
        $request->validate(['programmer_id'=>'required','storekeeper_remarks'=>'required']); $old=$repair_request->status;
        $repair_request->update(['status'=>'Forwarded to Programmer','storekeeper_id'=>Auth::id(),'programmer_id'=>$request->programmer_id,'storekeeper_remarks'=>$request->storekeeper_remarks]);
        RepairLog::create(['repair_request_id'=>$repair_request->id,'action_by'=>Auth::id(),'action'=>'Verified and forwarded','old_status'=>$old,'new_status'=>'Forwarded to Programmer','remarks'=>$request->storekeeper_remarks]);
        return redirect()->route('storekeeper.requests')->with('success','Request forwarded to programmer.');
    }
}
