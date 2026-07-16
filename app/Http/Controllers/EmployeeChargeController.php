<?php

namespace App\Http\Controllers;

use App\Employee;
use App\EmployeeCharge;
use Illuminate\Http\Request;
use App\Support\AccessScope;

class EmployeeChargeController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:admin,college_admin,department_admin,director']);
    }

    public function store(Request $request, Employee $employee)
    {
        if (!AccessScope::canAccessEmployee($employee)) abort(403);
        $data = $request->validate([
            'charge_name' => 'required|string|max:255',
            'charge_type' => 'nullable|string|max:255',
            'from_date' => 'nullable|date',
            'to_date' => 'nullable|date|after_or_equal:from_date',
            'is_active' => 'nullable|boolean',
            'remarks' => 'nullable|string',
        ]);
        $data['is_active'] = $request->has('is_active');
        $employee->charges()->create($data);
        return redirect()->route('employees.show', $employee)->with('success', 'Additional charge added successfully.');
    }

    public function destroy(Employee $employee, EmployeeCharge $charge)
    {
        if (!AccessScope::canAccessEmployee($employee)) abort(403);
        if ($charge->employee_id != $employee->id) abort(404);
        $charge->delete();
        return redirect()->route('employees.show', $employee)->with('success', 'Charge deleted.');
    }
}
