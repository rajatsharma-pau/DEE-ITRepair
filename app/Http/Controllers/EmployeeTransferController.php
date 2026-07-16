<?php

namespace App\Http\Controllers;

use App\Department;
use App\Employee;
use App\EmployeeServiceMovement;
use App\EmployeeTransfer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Support\AccessScope;

class EmployeeTransferController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:admin,college_admin,department_admin,director']);
    }

    public function store(Request $request, Employee $employee)
    {
        if (!AccessScope::canAccessEmployee($employee)) abort(403);
        $data = $request->validate([
            'to_college_id' => 'required|exists:colleges,id',
            'to_department_id' => 'required|exists:departments,id',
            'transfer_date' => 'required|date',
            'relieving_date' => 'nullable|date',
            'joining_date' => 'nullable|date',
            'order_no' => 'nullable|string|max:255',
            'order_date' => 'nullable|date',
            'order_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:4096',
            'remarks' => 'nullable|string',
        ]);

        if (!AccessScope::canAccessDepartment($data['to_department_id'])) abort(403, 'Target department is outside your scope.');
        $dept = Department::findOrFail($data['to_department_id']);
        $data['to_college_id'] = $dept->college_id; // keep correct college of selected department
        $data['employee_id'] = $employee->id;
        $data['from_college_id'] = $employee->college_id;
        $data['from_department_id'] = $employee->department_id;
        $data['created_by'] = Auth::id();

        if ($request->hasFile('order_file')) {
            $data['order_file'] = $request->file('order_file')->store('employee_transfer_orders', 'public');
        }

        $transfer = EmployeeTransfer::create($data);

        $employee->update([
            'college_id' => $data['to_college_id'],
            'department_id' => $data['to_department_id'],
            'status' => 'Active',
        ]);

        EmployeeServiceMovement::create([
            'employee_id' => $employee->id,
            'movement_type' => 'Transfer',
            'effective_date' => $data['transfer_date'],
            'order_no' => $data['order_no'] ?? null,
            'order_date' => $data['order_date'] ?? null,
            'document' => $data['order_file'] ?? null,
            'remarks' => 'Transferred from '.optional($transfer->fromDepartment)->name.' to '.optional($transfer->toDepartment)->name.'. '.$request->remarks,
            'created_by' => Auth::id(),
        ]);

        return redirect()->route('employees.show', $employee)->with('success', 'Employee transferred successfully and transfer history has been saved.');
    }

    public function destroy(Employee $employee, EmployeeTransfer $transfer)
    {
        if (!AccessScope::canAccessEmployee($employee)) abort(403);
        if ($transfer->employee_id != $employee->id) {
            abort(404);
        }
        if ($transfer->order_file) {
            Storage::disk('public')->delete($transfer->order_file);
        }
        $transfer->delete();
        return redirect()->route('employees.show', $employee)->with('success', 'Transfer record deleted. Current posting is not changed automatically.');
    }
}
