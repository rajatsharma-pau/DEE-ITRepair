<?php

namespace App\Http\Controllers;

use App\College;
use App\Department;
use App\Employee;
use App\Support\AccessScope;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class EmployeeTransferController extends Controller
{
    public function create(Employee $employee)
    {
        if (!AccessScope::canTransferEmployee($employee)) {
            abort(403, 'You are not allowed to transfer this employee.');
        }

        $colleges = AccessScope::transferDestinationColleges();
        $departments = AccessScope::transferDestinationDepartments();

        return view('employees.transfer', compact('employee', 'colleges', 'departments'));
    }

    public function store(Request $request, Employee $employee)
    {
        if (!AccessScope::canTransferEmployee($employee)) {
            abort(403, 'You are not allowed to transfer this employee.');
        }

        $request->validate([
            'to_college_id' => 'required|exists:colleges,id',
            'to_department_id' => 'required|exists:departments,id',
            'transfer_date' => 'required|date',
            'relieving_date' => 'nullable|date',
            'joining_date' => 'nullable|date',
            'order_no' => 'nullable|string|max:100',
            'order_date' => 'nullable|date',
            'remarks' => 'nullable|string|max:1000',
            'order_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png|max:2048',
        ]);

        $toDepartment = Department::where('id', $request->to_department_id)
            ->where('college_id', $request->to_college_id)
            ->firstOrFail();

        $fromCollegeId = $employee->college_id;
        $fromDepartmentId = $employee->department_id;

        $orderFilePath = null;
        if ($request->hasFile('order_file')) {
            $orderFilePath = $request->file('order_file')->store('employee_transfer_orders', 'public');
        }

        DB::transaction(function () use ($request, $employee, $fromCollegeId, $fromDepartmentId, $orderFilePath) {
            if (Schema::hasTable('employee_transfers')) {
                $data = [
                    'employee_id' => $employee->id,
                    'from_college_id' => $fromCollegeId,
                    'from_department_id' => $fromDepartmentId,
                    'to_college_id' => $request->to_college_id,
                    'to_department_id' => $request->to_department_id,
                    'transfer_date' => $request->transfer_date,
                    'relieving_date' => $request->relieving_date,
                    'joining_date' => $request->joining_date,
                    'order_no' => $request->order_no,
                    'order_date' => $request->order_date,
                    'order_file' => $orderFilePath,
                    'remarks' => $request->remarks,
                    'created_by' => Auth::id(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ];

                // Keep only columns that actually exist in your installation.
                $insert = [];
                foreach ($data as $key => $value) {
                    if (Schema::hasColumn('employee_transfers', $key)) {
                        $insert[$key] = $value;
                    }
                }
                DB::table('employee_transfers')->insert($insert);
            }

            $employee->college_id = $request->to_college_id;
            $employee->department_id = $request->to_department_id;
            if (Schema::hasColumn('employees', 'status')) {
                $employee->status = 'Active';
            }
            $employee->save();

            // Keep login scope in sync with employee current posting.
            if ($employee->user) {
                $employee->user->college_id = $request->to_college_id;
                $employee->user->department_id = $request->to_department_id;
                $employee->user->save();
            }
        });

        return redirect()->route('employees.show', $employee)->with('success', 'Employee transferred successfully.');
    }
}
