<?php
namespace App\Http\Controllers;
use App\Employee; use App\RepairRequest; use Illuminate\Support\Facades\Auth;
class DashboardController extends Controller
{
    public function index(){
        $user = Auth::user();
        $data['employees'] = Employee::count();
        $data['requests'] = RepairRequest::count();
        $data['submitted'] = RepairRequest::where('status','Submitted')->count();
        $data['programmer'] = RepairRequest::whereIn('status',['Forwarded to Programmer','In Progress','Need Part'])->count();
        if($user->role == 'employee' && $user->employee) $data['myRequests'] = RepairRequest::where('employee_id',$user->employee->id)->latest()->get(); else $data['myRequests'] = collect();
        return view('dashboard.index', $data);
    }
}
