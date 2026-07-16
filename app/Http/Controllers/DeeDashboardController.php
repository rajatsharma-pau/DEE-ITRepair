<?php
namespace App\Http\Controllers;
use App\Models\DeeEmployee;
use App\Models\DeeRepairRequest;
use Illuminate\Support\Facades\Auth;
class DeeDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('dee')->user();
        $data = [];
        if ($user->role == 'employee') {
            $data['my_pending'] = DeeRepairRequest::where('employee_id',$user->id)->whereNotIn('status',['closed','rejected'])->count();
            $data['my_closed'] = DeeRepairRequest::where('employee_id',$user->id)->where('status','closed')->count();
        } else {
            $data['employees'] = DeeEmployee::count();
            $data['submitted'] = DeeRepairRequest::where('status','submitted')->count();
            $data['forwarded'] = DeeRepairRequest::where('status','forwarded_to_programmer')->count();
            $data['closed'] = DeeRepairRequest::where('status','closed')->count();
        }
        return view('dashboard.index', compact('user','data'));
    }
}
