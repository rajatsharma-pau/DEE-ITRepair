<?php

namespace App\Http\Controllers;

use App\City;
use App\College;
use App\Country;
use App\Department;
use App\Designation;
use App\Employee;
use App\Section;
use App\State;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Schema;
use App\Support\AccessScope;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('role:admin,college_admin,department_admin,director')->except(['show']);
    }

    public function index(Request $request)
    {
        $query = AccessScope::apply(
            Employee::with(['user', 'designation', 'section', 'college', 'department', 'activeCharges'])
        );

        $search = trim($request->get('search', ''));

        if ($search !== '') {
            $phoneSearch = preg_replace('/\D+/', '', $search);

            $query->where(function ($q) use ($search, $phoneSearch) {
                $employeeColumns = [
                    'full_name',
                    'first_name',
                    'middle_name',
                    'last_name',
                    'phone',
                    'employee_code',
                    'gpf_no',
                    'nps_no',
                    'pan_no',
                    'pf_no',
                    'personal_file_no',
                    'aadhaar_no'
                ];

                foreach ($employeeColumns as $column) {
                    if (Schema::hasColumn('employees', $column)) {
                        $q->orWhere('employees.' . $column, 'like', '%' . $search . '%');
                    }
                }

                if ($phoneSearch !== '' && Schema::hasColumn('employees', 'phone')) {
                    $q->orWhere('employees.phone', 'like', '%' . $phoneSearch . '%');
                }

                $q->orWhereRaw(
                    "CONCAT_WS(' ', first_name, middle_name, last_name) LIKE ?",
                    ['%' . $search . '%']
                );

                $q->orWhereRaw(
                    "CONCAT_WS(' ', salutation, first_name, middle_name, last_name) LIKE ?",
                    ['%' . $search . '%']
                );

                $q->orWhereHas('user', function ($uq) use ($search, $phoneSearch) {
                    $uq->where(function ($inner) use ($search, $phoneSearch) {
                        if (Schema::hasColumn('users', 'name')) {
                            $inner->orWhere('users.name', 'like', '%' . $search . '%');
                        }

                        if (Schema::hasColumn('users', 'phone')) {
                            $inner->orWhere('users.phone', 'like', '%' . $search . '%');

                            if ($phoneSearch !== '') {
                                $inner->orWhere('users.phone', 'like', '%' . $phoneSearch . '%');
                            }
                        }

                        if (Schema::hasColumn('users', 'email')) {
                            $inner->orWhere('users.email', 'like', '%' . $search . '%');
                        }
                    });
                });

                $q->orWhereHas('designation', function ($dq) use ($search) {
                    $dq->where('name', 'like', '%' . $search . '%');
                });

                $q->orWhereHas('college', function ($cq) use ($search) {
                    $cq->where('name', 'like', '%' . $search . '%');
                });

                $q->orWhereHas('department', function ($dq) use ($search) {
                    $dq->where('name', 'like', '%' . $search . '%');
                });
            });
        }

        if ($request->filled('college_id')) {
            $query->where('college_id', $request->college_id);
        }

        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $employees = $query
            ->orderBy('first_name')
            ->orderBy('middle_name')
            ->orderBy('last_name')
            ->paginate(20)
            ->appends($request->query());

        $colleges = AccessScope::colleges();
        $departments = AccessScope::departments();

        return view('employees.index', compact('employees', 'colleges', 'departments', 'search'));
    }

    public function create()
    {
        $deeCollege = College::where('name', 'Directorate of Extension Education')->first();
        $employee = new Employee([
            'retirement_age' => 60,
            'manual_country' => 'India',
            'manual_state' => 'Punjab',
            'manual_city' => 'Ludhiana',
            'college_id' => AccessScope::collegeId() ?: optional($deeCollege)->id,
            'department_id' => AccessScope::departmentId(),
        ]);
        $data = $this->formData();
        return view('employees.create', compact('employee') + $data);
    }

    public function store(Request $request)
    {
        $data = $this->validatedData($request);
        $data = AccessScope::forceEmployeeScopeData($data);
        $roles = $this->selectedRoles($request);
        $this->authorizeRoleSelection($roles);
        $primaryRole = AccessScope::primaryRoleFrom($roles);
        $data = $this->syncCollegeFromDepartment($data);
        $data = AccessScope::forceEmployeeScopeData($data);
        $data = $this->calculateDates($data);
        $data['full_name'] = $this->makeFullName($data);

        if ($request->hasFile('photo')) {
            $data['photo'] = $request->file('photo')->store('employee_photos', 'public');
        }

        if (!empty($data['phone'])) {
            $user = User::create([
                'name' => $data['full_name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($request->password ?: 'password'),
                'role' => $primaryRole,
                'college_id' => $this->userCollegeScope($primaryRole, $data),
                'department_id' => $this->userDepartmentScope($primaryRole, $data),
                'is_active' => $request->has('is_active') ? 1 : 0,
                'must_change_password' => 1,
            ]);
            $user->syncRoleNames($roles, $this->userCollegeScope($primaryRole, $data), $this->userDepartmentScope($primaryRole, $data));
            $data['user_id'] = $user->id;
        }

        $employee = Employee::create($data);
        return redirect()->route('employees.show', $employee)->with('success', 'Employee created successfully.');
    }

    public function show(Employee $employee)
    {
        $user = Auth::user();
        if (!AccessScope::canAccessEmployee($employee, $user)) {
            abort(403);
        }
        $employee->load([
            'user',
            'designation',
            'section',
            'college',
            'department',
            'country',
            'state',
            'city',
            'serviceMovements.fromDesignation',
            'serviceMovements.toDesignation',
            'charges',
            'transfers.fromCollege',
            'transfers.fromDepartment',
            'transfers.toCollege',
            'transfers.toDepartment',
            'assets'
        ]);
        $designations = Designation::where('is_active', 1)->orderBy('sort_order')->orderBy('name')->get();
        // Transfer destination must show all colleges/departments because
        // Department Admin can transfer own department employee anywhere.
        $colleges = AccessScope::transferDestinationColleges();
        $departments = AccessScope::transferDestinationDepartments();
        return view('employees.show', compact('employee', 'designations', 'colleges', 'departments'));
    }

    public function edit(Employee $employee)
    {
        if (!AccessScope::canAccessEmployee($employee)) abort(403);
        $data = $this->formData();
        return view('employees.edit', compact('employee') + $data);
    }

    public function update(Request $request, Employee $employee)
    {
        if (!AccessScope::canAccessEmployee($employee)) abort(403);
        $data = $this->validatedData($request, $employee->id, optional($employee->user)->id);
        $data = AccessScope::forceEmployeeScopeData($data);
        $roles = $this->selectedRoles($request, $employee->user);
        $this->authorizeRoleSelection($roles);
        $primaryRole = AccessScope::primaryRoleFrom($roles);
        $data = $this->syncCollegeFromDepartment($data);
        $data = AccessScope::forceEmployeeScopeData($data);
        $data = $this->calculateDates($data);
        $data['full_name'] = $this->makeFullName($data);

        if ($request->hasFile('photo')) {
            if ($employee->photo) {
                Storage::disk('public')->delete($employee->photo);
            }
            $data['photo'] = $request->file('photo')->store('employee_photos', 'public');
        }

        $employee->update($data);

        if ($employee->user) {
            $employee->user->update([
                'name' => $data['full_name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'role' => $primaryRole,
                'college_id' => $this->userCollegeScope($primaryRole, $data),
                'department_id' => $this->userDepartmentScope($primaryRole, $data),
                'is_active' => $request->has('is_active') ? 1 : 0,
            ]);
            $employee->user->syncRoleNames($roles, $this->userCollegeScope($primaryRole, $data), $this->userDepartmentScope($primaryRole, $data));
            if ($request->filled('password')) {
                $employee->user->update(['password' => Hash::make($request->password), 'must_change_password' => 1]);
            }
        } elseif (!empty($data['phone'])) {
            $user = User::create([
                'name' => $data['full_name'],
                'phone' => $data['phone'],
                'email' => $data['email'] ?? null,
                'password' => Hash::make($request->password ?: 'password'),
                'role' => $primaryRole,
                'college_id' => $this->userCollegeScope($primaryRole, $data),
                'department_id' => $this->userDepartmentScope($primaryRole, $data),
                'is_active' => $request->has('is_active') ? 1 : 0,
                'must_change_password' => 1,
            ]);
            $user->syncRoleNames($roles, $this->userCollegeScope($primaryRole, $data), $this->userDepartmentScope($primaryRole, $data));
            $employee->update(['user_id' => $user->id]);
        }

        return redirect()->route('employees.show', $employee)->with('success', 'Employee updated successfully.');
    }

    public function destroy(Employee $employee)
    {
        if (!AccessScope::canAccessEmployee($employee)) abort(403);
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Employee deleted successfully.');
    }

    private function formData()
    {
        return [
            'colleges' => AccessScope::colleges(),
            'departments' => AccessScope::departments(),
            'sections' => Section::where('is_active', 1)->orderBy('name')->get(),
            'designations' => Designation::where('is_active', 1)->orderBy('sort_order')->orderBy('name')->get(),
            'countries' => Country::where('is_active', 1)->orderBy('name')->get(),
            'states' => State::where('is_active', 1)->orderBy('name')->get(),
            'cities' => City::where('is_active', 1)->orderBy('name')->get(),
        ];
    }

    private function validatedData(Request $request, $employeeId = null, $userId = null)
    {
        return $request->validate([
            'user_id' => 'nullable|exists:users,id',
            'college_id' => 'nullable|exists:colleges,id',
            'department_id' => 'nullable|exists:departments,id',
            'section_id' => 'nullable|exists:sections,id',
            'designation_id' => 'nullable|exists:designations,id',
            'employee_code' => 'nullable|string|max:100|unique:employees,employee_code,' . ($employeeId ?: 'NULL') . ',id',
            'salutation' => 'nullable|string|max:20',
            'first_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20|unique:users,phone,' . ($userId ?: 'NULL') . ',id',
            'email' => 'nullable|email|max:255|unique:users,email,' . ($userId ?: 'NULL') . ',id',
            'gpf_no' => 'nullable|string|max:100',
            'nps_no' => 'nullable|string|max:100',
            'pan_no' => 'nullable|string|max:20',
            'aadhaar_no' => 'nullable|string|max:20',
            'salary_account_no' => 'nullable|string|max:50',
            'job_type' => 'required|in:Permanent,Adhoc,Temporary,Daily Wages',
            'date_of_birth' => 'nullable|date',
            'date_of_joining' => 'nullable|date',
            'retirement_age' => 'nullable|integer|min:50|max:70',
            'manual_retirement_date' => 'nullable|date',
            'manual_increment_date' => 'nullable|date',
            'increment_remarks' => 'nullable|string|max:255',
            'manual_designation' => 'nullable|string|max:255',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'room_no' => 'nullable|string|max:100',
            'address_line_1' => 'nullable|string|max:255',
            'address_line_2' => 'nullable|string|max:255',
            'country_id' => 'nullable|exists:countries,id',
            'state_id' => 'nullable|exists:states,id',
            'city_id' => 'nullable|exists:cities,id',
            'manual_country' => 'nullable|string|max:255',
            'manual_state' => 'nullable|string|max:255',
            'manual_city' => 'nullable|string|max:255',
            'zip' => 'nullable|string|max:20',
            'status' => 'required|in:Active,Retired,Transferred,Inactive',
            'remarks' => 'nullable|string',
        ]);
    }

    private function selectedRoles(Request $request, $existingUser = null)
    {
        $roles = $request->input('roles', []);

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        if (empty($roles) && $request->filled('role')) {
            $roles = [$request->role];
        }

        if (empty($roles) && $existingUser) {
            $roles = $existingUser->roleNames();
        }

        if (empty($roles)) {
            $roles = ['employee'];
        }

        $roles = array_map(function ($role) {
            return AccessScope::normalizeRole($role);
        }, $roles);

        return array_values(array_unique(array_filter($roles)));
    }

    private function authorizeRoleSelection($roles)
    {
        $roles = is_array($roles) ? $roles : [$roles];

        // AccessScope::roleOptions() returns: slug => label.
        // Earlier code used in_array($role, $allowed), which compared slug with label
        // and blocked Superuser from assigning valid roles like superuser/admin.
        $allowedSlugs = AccessScope::allowedAssignableRoles();
        $allowedSlugs = array_map(function ($role) {
            return AccessScope::normalizeRole($role);
        }, $allowedSlugs);

        foreach ($roles as $role) {
            $role = AccessScope::normalizeRole($role);

            if (!in_array($role, $allowedSlugs)) {
                abort(403, 'You cannot assign this role: ' . $role);
            }
        }
    }

    private function userCollegeScope($role, array $data)
    {
        if ($role == 'superuser') return null;
        return $data['college_id'] ?? null;
    }

    private function userDepartmentScope($role, array $data)
    {
        if (in_array($role, ['superuser', 'college_admin', 'director'])) return null;
        return $data['department_id'] ?? null;
    }

    private function syncCollegeFromDepartment($data)
    {
        // Force own scope for Department Admin before checking posted values.
        // This prevents blank/wrong college-department values and makes Add Employee
        // work for Department Admin + Employee users.
        $data = AccessScope::forceEmployeeScopeData($data);

        if (!empty($data['department_id']) && !AccessScope::canAccessDepartment($data['department_id'])) {
            abort(403, 'Selected department is outside your scope.');
        }

        if (!empty($data['college_id']) && !AccessScope::canAccessCollege($data['college_id'])) {
            abort(403, 'Selected college is outside your scope.');
        }

        if (!empty($data['department_id'])) {
            $dept = Department::find($data['department_id']);
            if ($dept) {
                $data['college_id'] = $dept->college_id;
            }
        }

        $data = AccessScope::forceEmployeeScopeData($data);

        return $data;
    }

    private function makeFullName($data)
    {
        return trim(($data['first_name'] ?? '') . ' ' . ($data['middle_name'] ?? '') . ' ' . ($data['last_name'] ?? ''));
    }

    private function calculateDates($data)
    {
        $retirementAge = isset($data['retirement_age']) ? (int)$data['retirement_age'] : 60;
        if (!empty($data['date_of_birth'])) {
            $data['calculated_retirement_date'] = Carbon::parse($data['date_of_birth'])->addYears($retirementAge)->endOfMonth()->format('Y-m-d');
        }
        $data['final_retirement_date'] = !empty($data['manual_retirement_date']) ? $data['manual_retirement_date'] : ($data['calculated_retirement_date'] ?? null);

        $baseDate = !empty($data['date_of_joining']) ? Carbon::parse($data['date_of_joining']) : null;
        if ($baseDate) {
            $candidate = Carbon::create(date('Y'), $baseDate->month, 1);
            if ($candidate->lt(Carbon::today())) {
                $candidate->addYear();
            }
            $data['calculated_increment_date'] = $candidate->format('Y-m-d');
        }
        $data['final_increment_date'] = !empty($data['manual_increment_date']) ? $data['manual_increment_date'] : ($data['calculated_increment_date'] ?? null);
        return $data;
    }
}
