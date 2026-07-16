<?php

namespace App\Http\Controllers;

use App\Designation;
use App\Employee;
use App\EmployeeServiceMovement;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Support\AccessScope;

class EmployeeServiceMovementController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:admin,college_admin,department_admin,director']);
    }

    public function store(Request $request, Employee $employee)
    {
        if (!AccessScope::canAccessEmployee($employee)) abort(403);
        $data = $request->validate([
            'movement_type' => 'required|in:Joining,Promotion,Transfer,Additional Charge,Reversion,Retirement,Resignation,Contract Extension,Other',
            'from_designation_id' => 'nullable|exists:designations,id',
            'to_designation_id' => 'nullable|exists:designations,id',
            'manual_from_designation' => 'nullable|string|max:255',
            'manual_to_designation' => 'nullable|string|max:255',
            'effective_date' => 'required|date',
            'order_no' => 'nullable|string|max:255',
            'order_date' => 'nullable|date',
            'document' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'remarks' => 'nullable|string',
        ]);

        if ($request->hasFile('document')) {
            $data['document_path'] = $request->file('document')->store('service_orders', 'public');
        }
        unset($data['document']);
        $data['created_by'] = Auth::id();

        EmployeeServiceMovement::create($data + ['employee_id' => $employee->id]);

        if (in_array($data['movement_type'], ['Joining','Promotion','Reversion']) && !empty($data['to_designation_id'])) {
            $employee->designation_id = $data['to_designation_id'];
            $employee->manual_designation = null;
        } elseif (in_array($data['movement_type'], ['Joining','Promotion','Reversion']) && !empty($data['manual_to_designation'])) {
            $employee->manual_designation = $data['manual_to_designation'];
        }

        if ($data['movement_type'] == 'Promotion') {
            $base = Carbon::parse($data['effective_date']);
            $candidate = Carbon::create(date('Y'), $base->month, 1);
            if ($candidate->lt(Carbon::today())) {
                $candidate->addYear();
            }
            $employee->calculated_increment_date = $candidate->format('Y-m-d');
            $employee->final_increment_date = $employee->manual_increment_date ?: $employee->calculated_increment_date;
        }

        if ($data['movement_type'] == 'Retirement') {
            $employee->status = 'Retired';
        }

        $employee->save();
        return redirect()->route('employees.show', $employee)->with('success', 'Service movement added successfully.');
    }

    public function destroy(Employee $employee, EmployeeServiceMovement $movement)
    {
        if (!AccessScope::canAccessEmployee($employee)) abort(403);
        if ($movement->employee_id != $employee->id) abort(404);
        if ($movement->document_path) Storage::disk('public')->delete($movement->document_path);
        $movement->delete();
        return redirect()->route('employees.show', $employee)->with('success', 'Service movement deleted.');
    }
}
