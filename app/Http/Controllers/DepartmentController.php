<?php

namespace App\Http\Controllers;

use App\Department;

class DepartmentController extends Controller
{
    public function byCollege($collegeId)
    {
        $departments = Department::where('college_id', $collegeId)
            ->where('is_active', 1)
            ->orderBy('name')
            ->get(['id','name','place']);

        return response()->json($departments);
    }
}
