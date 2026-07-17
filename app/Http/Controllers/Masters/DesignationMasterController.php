<?php

namespace App\Http\Controllers\Masters;

use App\Designation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DesignationMasterController extends MasterBaseController
{
    public function index(Request $request)
    {
        $query = Designation::query()->orderBy('cadre')->orderBy('sort_order')->orderBy('name');

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', '%' . $search . '%')
                  ->orWhere('cadre', 'like', '%' . $search . '%');
            });
        }

        if ($request->filled('cadre')) {
            $query->where('cadre', $request->cadre);
        }

        if ($request->filled('status') && Schema::hasColumn('designations', 'is_active')) {
            $query->where('is_active', $request->status === 'active' ? 1 : 0);
        }

        $designations = $query->paginate(30)->appends($request->query());
        $cadres = Designation::whereNotNull('cadre')->distinct()->orderBy('cadre')->pluck('cadre');
        return view('masters.designations.index', compact('designations', 'cadres'));
    }

    public function create()
    {
        return view('masters.designations.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:designations,name',
            'cadre' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $designation = new Designation();
        $designation->name = trim($request->name);
        $designation->cadre = $request->cadre ?: 'Administrative';
        $designation->sort_order = $request->sort_order ?: 999;
        if (Schema::hasColumn('designations', 'is_active')) {
            $designation->is_active = $request->has('is_active') ? 1 : 0;
        }
        $designation->save();

        return redirect()->route('master.designations.index')->with('success', 'Designation added successfully.');
    }

    public function edit($id)
    {
        $designation = Designation::findOrFail($id);
        return view('masters.designations.edit', compact('designation'));
    }

    public function update(Request $request, $id)
    {
        $designation = Designation::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255|unique:designations,name,' . $designation->id,
            'cadre' => 'nullable|string|max:100',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $designation->name = trim($request->name);
        $designation->cadre = $request->cadre ?: 'Administrative';
        $designation->sort_order = $request->sort_order ?: 999;
        if (Schema::hasColumn('designations', 'is_active')) {
            $designation->is_active = $request->has('is_active') ? 1 : 0;
        }
        $designation->save();

        return redirect()->route('master.designations.index')->with('success', 'Designation updated successfully.');
    }

    public function destroy($id)
    {
        $designation = Designation::findOrFail($id);

        $result = $this->deactivateOrDelete($designation, [
            ['employees', 'designation_id'],
            ['employee_service_movements', 'from_designation_id'],
            ['employee_service_movements', 'to_designation_id'],
        ]);

        return redirect()->route('master.designations.index')->with($result['type'], $result['message']);
    }
}
