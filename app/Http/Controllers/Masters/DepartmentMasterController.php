<?php

namespace App\Http\Controllers\Masters;

use App\College;
use App\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class DepartmentMasterController extends MasterBaseController
{
    public function index(Request $request)
    {
        $query = Department::with('college')->orderBy('college_id')->orderBy('name');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('place', 'like', '%' . $search . '%')
                  ->orWhereHas('college', function ($cq) use ($search) {
                      $cq->where('name', 'like', '%' . $search . '%');
                  });
            });
        }

        if ($request->filled('college_id')) {
            $query->where('college_id', $request->college_id);
        }

        if ($request->filled('status') && Schema::hasColumn('departments', 'is_active')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $departments = $query->paginate(25)->appends($request->query());
        $colleges = College::orderBy('name')->get();
        return view('masters.departments.index', compact('departments', 'colleges'));
    }

    public function create()
    {
        $colleges = College::orderBy('name')->get();
        return view('masters.departments.create', compact('colleges'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'college_id' => 'required|exists:colleges,id',
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('departments')->where(function ($q) use ($request) {
                    return $q->where('college_id', $request->college_id);
                }),
            ],
            'place' => 'nullable|string|max:255',
        ]);

        $department = new Department();
        $department->college_id = $request->college_id;
        $department->name = trim($request->name);
        $department->place = $request->place ?: 'Ludhiana';
        if (Schema::hasColumn('departments', 'is_active')) {
            $department->is_active = $request->has('is_active') ? 1 : 0;
        }
        $department->save();

        return redirect()->route('master.departments.index')->with('success', 'Department / Office / KVK added successfully.');
    }

    public function edit($id)
    {
        $department = Department::findOrFail($id);
        $colleges = College::orderBy('name')->get();
        return view('masters.departments.edit', compact('department', 'colleges'));
    }

    public function update(Request $request, $id)
    {
        $department = Department::findOrFail($id);

        $request->validate([
            'college_id' => 'required|exists:colleges,id',
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('departments')->ignore($department->id)->where(function ($q) use ($request) {
                    return $q->where('college_id', $request->college_id);
                }),
            ],
            'place' => 'nullable|string|max:255',
        ]);

        $department->college_id = $request->college_id;
        $department->name = trim($request->name);
        $department->place = $request->place ?: 'Ludhiana';
        if (Schema::hasColumn('departments', 'is_active')) {
            $department->is_active = $request->has('is_active') ? 1 : 0;
        }
        $department->save();

        return redirect()->route('master.departments.index')->with('success', 'Department / Office / KVK updated successfully.');
    }

    public function destroy($id)
    {
        $department = Department::findOrFail($id);

        $result = $this->deactivateOrDelete($department, [
            ['employees', 'department_id'],
            ['users', 'department_id'],
            ['assets', 'department_id'],
            ['store_items', 'department_id'],
            ['repair_requests', 'department_id'],
            ['store_indents', 'department_id'],
            ['employee_transfers', 'from_department_id'],
            ['employee_transfers', 'to_department_id'],
        ]);

        return redirect()->route('master.departments.index')->with($result['type'], $result['message']);
    }
}
