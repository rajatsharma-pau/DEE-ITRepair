<?php
// Add this at the top of app/Http/Controllers/EmployeeController.php:
// use Illuminate\Support\Facades\Schema;

public function index(\Illuminate\Http\Request $request)
{
    $search = trim($request->input('search', $request->input('q', '')));
    $collegeId = $request->input('college_id');
    $departmentId = $request->input('department_id');
    $status = $request->input('status');

    $query = \App\Employee::query()
        ->with(['user', 'designation', 'college', 'department'])
        ->orderBy('full_name', 'asc');

    // Scope data for logged-in admin/department users.
    $loginUser = auth()->user();
    if ($loginUser) {
        $isSuperUser = method_exists($loginUser, 'isSuperUser')
            ? $loginUser->isSuperUser()
            : (isset($loginUser->role) && $loginUser->role === 'superuser');

        $hasAnyRole = function ($roles) use ($loginUser) {
            if (method_exists($loginUser, 'hasAnyRole')) {
                return $loginUser->hasAnyRole($roles);
            }
            return isset($loginUser->role) && in_array($loginUser->role, $roles);
        };

        if (!$isSuperUser) {
            if ($hasAnyRole(['college_admin', 'director']) && !empty($loginUser->college_id)) {
                $query->where('college_id', $loginUser->college_id);
            } elseif ($hasAnyRole(['admin', 'department_admin', 'storekeeper', 'programmer', 'd4_seat']) && !empty($loginUser->department_id)) {
                $query->where('department_id', $loginUser->department_id);
            }
        }
    }

    if (!empty($collegeId)) {
        $query->where('college_id', $collegeId);
    }

    if (!empty($departmentId)) {
        $query->where('department_id', $departmentId);
    }

    if (!empty($status)) {
        $query->where('status', $status);
    }

    if ($search !== '') {
        $like = '%' . $search . '%';

        $query->where(function ($q) use ($like) {
            $employeeColumns = [
                'full_name', 'first_name', 'middle_name', 'last_name', 'phone',
                'employee_code', 'personal_file_no', 'pf_no', 'gpf_no', 'nps_no',
                'pan_no', 'aadhaar_no', 'salary_account_no'
            ];

            foreach ($employeeColumns as $column) {
                if (Schema::hasColumn('employees', $column)) {
                    $q->orWhere($column, 'like', $like);
                }
            }

            $q->orWhereHas('user', function ($uq) use ($like) {
                $userColumns = ['name', 'phone', 'mobile', 'email', 'login_id'];
                foreach ($userColumns as $column) {
                    if (Schema::hasColumn('users', $column)) {
                        $uq->orWhere($column, 'like', $like);
                    }
                }
            });

            $q->orWhereHas('designation', function ($dq) use ($like) {
                $dq->where('name', 'like', $like);
            });

            $q->orWhereHas('college', function ($cq) use ($like) {
                $cq->where('name', 'like', $like);
            });

            $q->orWhereHas('department', function ($dq) use ($like) {
                $dq->where('name', 'like', $like)
                   ->orWhere('place', 'like', $like);
            });
        });
    }

    $employees = $query->paginate(25)->appends($request->query());

    $colleges = \App\College::orderBy('name', 'asc')->get();
    $departments = \App\Department::when($collegeId, function ($q) use ($collegeId) {
            return $q->where('college_id', $collegeId);
        })
        ->orderBy('name', 'asc')
        ->get();

    return view('employees.index', compact('employees', 'colleges', 'departments', 'search', 'collegeId', 'departmentId', 'status'));
}
