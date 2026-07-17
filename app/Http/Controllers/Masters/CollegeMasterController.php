<?php

namespace App\Http\Controllers\Masters;

use App\College;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class CollegeMasterController extends MasterBaseController
{
    public function index(Request $request)
    {
        $query = College::query()->orderBy('name');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where('name', 'like', '%' . $search . '%');
        }

        if ($request->filled('status') && Schema::hasColumn('colleges', 'is_active')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $colleges = $query->paginate(25)->appends($request->query());
        return view('masters.colleges.index', compact('colleges'));
    }

    public function create()
    {
        return view('masters.colleges.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:colleges,name',
            'short_name' => 'nullable|string|max:100',
        ]);

        $college = new College();
        $college->name = trim($request->name);
        if (Schema::hasColumn('colleges', 'short_name')) {
            $college->short_name = $request->short_name;
        }
        if (Schema::hasColumn('colleges', 'is_active')) {
            $college->is_active = $request->has('is_active') ? 1 : 0;
        }
        $college->save();

        return redirect()->route('master.colleges.index')->with('success', 'College / Directorate added successfully.');
    }

    public function edit($id)
    {
        $college = College::findOrFail($id);
        return view('masters.colleges.edit', compact('college'));
    }

    public function update(Request $request, $id)
    {
        $college = College::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:colleges,name,' . $college->id,
            'short_name' => 'nullable|string|max:100',
        ]);

        $college->name = trim($request->name);
        if (Schema::hasColumn('colleges', 'short_name')) {
            $college->short_name = $request->short_name;
        }
        if (Schema::hasColumn('colleges', 'is_active')) {
            $college->is_active = $request->has('is_active') ? 1 : 0;
        }
        $college->save();

        return redirect()->route('master.colleges.index')->with('success', 'College / Directorate updated successfully.');
    }

    public function destroy($id)
    {
        $college = College::findOrFail($id);

        $result = $this->deactivateOrDelete($college, [
            ['departments', 'college_id'],
            ['employees', 'college_id'],
            ['users', 'college_id'],
            ['assets', 'college_id'],
            ['store_items', 'college_id'],
            ['repair_requests', 'college_id'],
            ['store_indents', 'college_id'],
            ['employee_transfers', 'from_college_id'],
            ['employee_transfers', 'to_college_id'],
        ]);

        return redirect()->route('master.colleges.index')->with($result['type'], $result['message']);
    }
}
