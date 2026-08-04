<?php

namespace App\Support;

class AccessScope
{
    protected static function currentUser($user = null)
    {
        return $user ?: auth()->user();
    }
public static function normalizeRole($role)
    {
        return strtolower(trim(str_replace(' ', '_', (string) $role)));
    }

    protected static function roleNames($user)
    {
        if (!$user) {
            return [];
        }

        $names = [];

        if (isset($user->role) && $user->role) {
            $names[] = $user->role;
        }

        try {
            if (method_exists($user, 'roleNames')) {
                $fromModel = $user->roleNames();
                if (is_array($fromModel)) {
                    $names = array_merge($names, $fromModel);
                }
            }
        } catch (\Exception $e) {
            // keep users.role fallback
        }

        $names = array_map(function ($role) {
            return self::normalizeRole($role);
        }, $names);

        return array_values(array_unique(array_filter($names)));
    }

    protected static function userHasRole($user, $role)
    {
        if (!$user) {
            return false;
        }

        return in_array(self::normalizeRole($role), self::roleNames($user));
    }

    protected static function userHasAnyRole($user, array $roles)
    {
        if (!$user) {
            return false;
        }

        $userRoles = self::roleNames($user);

        foreach ($roles as $role) {
            if (in_array(self::normalizeRole($role), $userRoles)) {
                return true;
            }
        }

        return false;
    }

    public static function isSuperuser($user = null)
    {
        $user = self::currentUser($user);
        return self::userHasRole($user, 'superuser');
    }

    public static function isEmployeeOnly($user = null)
    {
        $user = self::currentUser($user);

        if (!$user) {
            return false;
        }

        // Important: if a user has Employee + Department Admin, Employee + Storekeeper,
        // etc., do NOT treat him/her as employee-only. The higher role must win.
        return !self::userHasAnyRole($user, [
            'superuser',
            'admin',
            'college_admin',
            'department_admin',
            'director',
            'storekeeper',
            'programmer',
            'store_incharge',
            'd4_seat',
        ]);
    }

    protected static function employeeScopeValue($user, $column)
    {
        if (!$user || !isset($user->employee) || !$user->employee) {
            return null;
        }

        return isset($user->employee->{$column}) && $user->employee->{$column}
            ? $user->employee->{$column}
            : null;
    }

    protected static function pivotScopeValue($user, $column)
    {
        if (!$user || !isset($user->id)) {
            return null;
        }

        try {
            if (!\Schema::hasTable('user_roles') || !\Schema::hasTable('roles')) {
                return null;
            }

            if (!\Schema::hasColumn('user_roles', $column)) {
                return null;
            }

            $query = \DB::table('user_roles')
                ->join('roles', 'roles.id', '=', 'user_roles.role_id')
                ->where('user_roles.user_id', $user->id)
                ->whereNotNull('user_roles.' . $column)
                ->select('user_roles.' . $column, 'roles.name', 'roles.slug');

            $rows = $query->get();

            if ($rows->isEmpty()) {
                return null;
            }

            $priority = [
                'department_admin',
                'college_admin',
                'admin',
                'director',
                'storekeeper',
                'programmer',
                'store_incharge',
                'd4_seat',
                'employee',
            ];

            foreach ($priority as $role) {
                foreach ($rows as $row) {
                    $name = isset($row->slug) && $row->slug ? $row->slug : $row->name;
                    if (self::normalizeRole($name) === $role) {
                        return $row->{$column};
                    }
                }
            }

            return $rows->first()->{$column};
        } catch (\Exception $e) {
            return null;
        }
    }

    public static function collegeId($user = null)
    {
        $user = self::currentUser($user);

        if (!$user) {
            return null;
        }

        if (isset($user->college_id) && $user->college_id) {
            return $user->college_id;
        }

        $fromEmployee = self::employeeScopeValue($user, 'college_id');
        if ($fromEmployee) {
            return $fromEmployee;
        }

        return self::pivotScopeValue($user, 'college_id');
    }

    public static function departmentId($user = null)
    {
        $user = self::currentUser($user);

        if (!$user) {
            return null;
        }

        if (isset($user->department_id) && $user->department_id) {
            return $user->department_id;
        }

        $fromEmployee = self::employeeScopeValue($user, 'department_id');
        if ($fromEmployee) {
            return $fromEmployee;
        }

        return self::pivotScopeValue($user, 'department_id');
    }

    public static function college($user = null)
    {
        $collegeId = self::collegeId($user);

        if (!$collegeId || !\Schema::hasTable('colleges')) {
            return null;
        }

        return \DB::table('colleges')->where('id', $collegeId)->first();
    }

    public static function department($user = null)
    {
        $departmentId = self::departmentId($user);

        if (!$departmentId || !\Schema::hasTable('departments')) {
            return null;
        }

        return \DB::table('departments')->where('id', $departmentId)->first();
    }

    public static function apply($query, $collegeColumn = 'college_id', $departmentColumn = 'department_id', $user = null)
    {
        $user = self::currentUser($user);

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if (self::isSuperuser($user)) {
            return $query;
        }

        // Department Admin can see employees of own department only,
        // even if he/she also has Employee role.
        if (self::userHasRole($user, 'department_admin')) {
            $departmentId = self::departmentId($user);
            return $departmentId ? $query->where($departmentColumn, $departmentId) : $query->whereRaw('1 = 0');
        }

        // College Admin/Admin/Director can see employees of own college/directorate only.
        if (self::userHasAnyRole($user, ['admin', 'college_admin', 'director'])) {
            $collegeId = self::collegeId($user);
            return $collegeId ? $query->where($collegeColumn, $collegeId) : $query->whereRaw('1 = 0');
        }

        if (self::isEmployeeOnly($user)) {
            if (isset($user->employee) && $user->employee && isset($user->employee->id)) {
                return $query->where('id', $user->employee->id);
            }
            return $query->whereRaw('1 = 0');
        }

        // Functional roles are scoped to their own department for employee records.
        $departmentId = self::departmentId($user);
        return $departmentId ? $query->where($departmentColumn, $departmentId) : $query->whereRaw('1 = 0');
    }

    public static function canAccessCollege($collegeId, $user = null)
    {
        $user = self::currentUser($user);

        if (!$user || !$collegeId) {
            return false;
        }

        if (self::isSuperuser($user)) {
            return true;
        }

        if (self::userHasAnyRole($user, [
            'admin',
            'college_admin',
            'director',
            'department_admin',
            'storekeeper',
            'programmer',
            'store_incharge',
            'd4_seat',
        ])) {
            return (int) self::collegeId($user) === (int) $collegeId;
        }

        return false;
    }

    public static function canAccessDepartment($departmentId, $user = null)
    {
        $user = self::currentUser($user);

        if (!$user || !$departmentId) {
            return false;
        }

        if (self::isSuperuser($user)) {
            return true;
        }

        if (!\Schema::hasTable('departments')) {
            return false;
        }

        $department = \DB::table('departments')->where('id', $departmentId)->first();

        if (!$department) {
            return false;
        }

        // Department Admin can add/edit employees only in own department.
        // Transfer destination anywhere is handled separately by canTransferEmployee().
        if (self::userHasRole($user, 'department_admin')) {
            return (int) self::departmentId($user) === (int) $departmentId;
        }

        if (self::userHasAnyRole($user, [
            'admin',
            'college_admin',
            'director',
            'storekeeper',
        ])) {
            return (int) self::collegeId($user) === (int) $department->college_id;
        }

        /*
         * Functional roles work only within their assigned department.
         * This includes Programmer, Store Incharge and D-4 Seat.
         */
        if (self::userHasAnyRole($user, [
            'programmer',
            'store_incharge',
            'd4_seat',
        ])) {
            return (int) self::departmentId($user) === (int) $departmentId;
        }

        return false;
    }

    public static function canAccessEmployee($employee, $user = null)
    {
        $user = self::currentUser($user);

        if (!$user || !$employee) {
            return false;
        }

        if (self::isSuperuser($user)) {
            return true;
        }

        if (self::userHasRole($user, 'department_admin')) {
            return isset($employee->department_id) && (int) $employee->department_id === (int) self::departmentId($user);
        }

        if (self::userHasAnyRole($user, ['admin', 'college_admin', 'director'])) {
            return isset($employee->college_id) && (int) $employee->college_id === (int) self::collegeId($user);
        }

        if (self::isEmployeeOnly($user)) {
            return isset($employee->user_id) && (int) $employee->user_id === (int) $user->id;
        }

        // Functional roles can view employees of own department only.
        if (self::userHasAnyRole($user, ['storekeeper', 'programmer', 'store_incharge', 'd4_seat'])) {
            return isset($employee->department_id) && (int) $employee->department_id === (int) self::departmentId($user);
        }

        return false;
    }

    public static function canEditEmployee($employee, $user = null)
    {
        $user = self::currentUser($user);

        if (!$user || !$employee) {
            return false;
        }

        if (self::isSuperuser($user)) {
            return true;
        }

        if (self::userHasRole($user, 'department_admin')) {
            return isset($employee->department_id) && (int) $employee->department_id === (int) self::departmentId($user);
        }

        if (self::userHasAnyRole($user, ['admin', 'college_admin', 'director'])) {
            return isset($employee->college_id) && (int) $employee->college_id === (int) self::collegeId($user);
        }

        return false;
    }

    public static function canTransferEmployee($employee, $user = null)
    {
        $user = self::currentUser($user);

        if (!$user || !$employee) {
            return false;
        }

        if (self::isSuperuser($user)) {
            return true;
        }

        // College Admin/Admin/Director can transfer employees currently belonging
        // to own college/directorate to any destination college/department.
        if (self::userHasAnyRole($user, ['admin', 'college_admin', 'director'])) {
            return isset($employee->college_id) && (int) $employee->college_id === (int) self::collegeId($user);
        }

        // Department Admin can transfer employees currently belonging to own
        // department to any destination college/department.
        if (self::userHasRole($user, 'department_admin')) {
            return isset($employee->department_id) && (int) $employee->department_id === (int) self::departmentId($user);
        }

        return false;
    }

    public static function canAssignRolesToEmployee($employee, $user = null)
    {
        $user = self::currentUser($user);

        if (!$user || !$employee) {
            return false;
        }

        if (self::isSuperuser($user)) {
            return true;
        }

        if (self::userHasAnyRole($user, ['admin', 'college_admin', 'director'])) {
            return isset($employee->college_id) && (int) $employee->college_id === (int) self::collegeId($user);
        }

        if (self::userHasRole($user, 'department_admin')) {
            return isset($employee->department_id) && (int) $employee->department_id === (int) self::departmentId($user);
        }

        return false;
    }

    public static function allowedAssignableRoles($employee = null, $user = null)
    {
        $user = self::currentUser($user);

        if (!$user) {
            return [];
        }

        if (self::isSuperuser($user)) {
            return [
                'superuser',
                'admin',
                'college_admin',
                'department_admin',
                'employee',
                'storekeeper',
                'programmer',
                'store_incharge',
                'd4_seat',
                'director',
            ];
        }

        if ($employee && !self::canAssignRolesToEmployee($employee, $user)) {
            return [];
        }

        if (self::userHasAnyRole($user, ['admin', 'college_admin', 'director'])) {
            return [
                'employee',
                'department_admin',
                'storekeeper',
                'programmer',
                'store_incharge',
                'd4_seat',
            ];
        }

        if (self::userHasRole($user, 'department_admin')) {
            return [
                'employee',
                'storekeeper',
                'programmer',
            ];
        }

        return [];
    }

    public static function roleOptions($employee = null, $user = null)
    {
        $allowed = self::allowedAssignableRoles($employee, $user);
        $options = [];

        if (\Schema::hasTable('roles')) {
            $roles = \DB::table('roles')
                ->where(function ($q) use ($allowed) {
                    $q->whereIn('slug', $allowed)->orWhereIn('name', $allowed);
                })
                ->orderBy('display_name')
                ->get();

            foreach ($roles as $role) {
                $slug = isset($role->slug) && $role->slug ? self::normalizeRole($role->slug) : self::normalizeRole($role->name);
                $label = isset($role->display_name) && $role->display_name ? $role->display_name : ucwords(str_replace('_', ' ', $slug));
                $options[$slug] = $label;
            }
        }

        foreach ($allowed as $slug) {
            if (!isset($options[$slug])) {
                $options[$slug] = ucwords(str_replace('_', ' ', $slug));
            }
        }

        return $options;
    }

    public static function primaryRoleFrom(array $roles)
    {
        $roles = array_map(function ($role) {
            return self::normalizeRole($role);
        }, $roles);

        $roles = array_values(array_unique(array_filter($roles)));

        if (empty($roles)) {
            return 'employee';
        }

        $priority = [
            'superuser',
            'admin',
            'college_admin',
            'director',
            'department_admin',
            'storekeeper',
            'programmer',
            'store_incharge',
            'd4_seat',
            'employee',
        ];

        foreach ($priority as $role) {
            if (in_array($role, $roles)) {
                return $role;
            }
        }

        return $roles[0];
    }

    public static function forceEmployeeScopeData(array $data, $user = null)
    {
        $user = self::currentUser($user);

        if (!$user || self::isSuperuser($user)) {
            return $data;
        }

        if (self::userHasRole($user, 'department_admin')) {
            $data['college_id'] = self::collegeId($user);
            $data['department_id'] = self::departmentId($user);
            return $data;
        }

        if (self::userHasAnyRole($user, ['admin', 'college_admin', 'director'])) {
            $data['college_id'] = self::collegeId($user);
            return $data;
        }

        return $data;
    }


    /**
     * Return employee query scoped for the logged-in user.
     * Used by AssetController and other forms where employee dropdown is needed.
     *
     * Rules:
     * - Superuser: all employees
     * - Admin / College Admin / Director: employees of own college/directorate
     * - Department Admin: employees of own department
     * - Employee-only: own employee record only
     * - Functional roles: own department employees
     */
    public static function employeesQuery($user = null)
    {
        $user = self::currentUser($user);

        $query = \App\Employee::query();

        if (!$user) {
            return $query->whereRaw('1 = 0');
        }

        if (self::isSuperuser($user)) {
            return $query;
        }

        if (self::userHasRole($user, 'department_admin')) {
            if (self::departmentId($user)) {
                return $query->where('department_id', self::departmentId($user));
            }
            return $query->whereRaw('1 = 0');
        }

        if (self::userHasAnyRole($user, ['admin', 'college_admin', 'director'])) {
            if (self::collegeId($user)) {
                return $query->where('college_id', self::collegeId($user));
            }
            return $query->whereRaw('1 = 0');
        }

        if (self::isEmployeeOnly($user)) {
            if (isset($user->employee) && $user->employee && isset($user->employee->id)) {
                return $query->where('id', $user->employee->id);
            }

            if (isset($user->id)) {
                return $query->where('user_id', $user->id);
            }

            return $query->whereRaw('1 = 0');
        }

        // Storekeeper / Programmer / Store Incharge / D-4 functional scope.
        if (self::departmentId($user)) {
            return $query->where('department_id', self::departmentId($user));
        }

        return $query->whereRaw('1 = 0');
    }

    public static function colleges($user = null)
    {
        $user = self::currentUser($user);

        if (!\Schema::hasTable('colleges')) {
            return collect([]);
        }

        $query = \DB::table('colleges');

        if (\Schema::hasColumn('colleges', 'is_active')) {
            $query->where('is_active', 1);
        }

        if (!$user) {
            return collect([]);
        }

        if (!self::isSuperuser($user)) {
            $collegeId = self::collegeId($user);
            if ($collegeId) {
                $query->where('id', $collegeId);
            } else {
                $query->whereRaw('1 = 0');
            }
        }

        return $query->orderBy('name')->get();
    }

    public static function departments($collegeId = null, $user = null)
    {
        $user = self::currentUser($user);

        if (!\Schema::hasTable('departments')) {
            return collect([]);
        }

        $query = \DB::table('departments');

        if (\Schema::hasColumn('departments', 'is_active')) {
            $query->where('is_active', 1);
        }

        if ($collegeId) {
            $query->where('college_id', $collegeId);
        }

        if (!$user) {
            return collect([]);
        }

        if (!self::isSuperuser($user)) {
            if (self::userHasRole($user, 'department_admin')) {
                $departmentId = self::departmentId($user);
                if ($departmentId) {
                    $query->where('id', $departmentId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            } else {
                $collegeId = self::collegeId($user);
                if ($collegeId) {
                    $query->where('college_id', $collegeId);
                } else {
                    $query->whereRaw('1 = 0');
                }
            }
        }

        return $query->orderBy('name')->get();
    }

    public static function transferDestinationColleges($user = null)
    {
        if (!\Schema::hasTable('colleges')) {
            return collect([]);
        }

        $query = \DB::table('colleges');

        if (\Schema::hasColumn('colleges', 'is_active')) {
            $query->where('is_active', 1);
        }

        return $query->orderBy('name')->get();
    }

    public static function transferDestinationDepartments($collegeId = null, $user = null)
    {
        if (!\Schema::hasTable('departments')) {
            return collect([]);
        }

        $query = \DB::table('departments');

        if ($collegeId) {
            $query->where('college_id', $collegeId);
        }

        if (\Schema::hasColumn('departments', 'is_active')) {
            $query->where('is_active', 1);
        }

        return $query->orderBy('name')->get();
    }

    public static function scopeLabel($user = null)
    {
        $user = self::currentUser($user);

        if (!$user) {
            return 'Scope: Not logged in';
        }

        if (self::isSuperuser($user)) {
            return 'Scope: All PAU / University';
        }

        $collegeName = 'Assigned College / Directorate';
        $departmentName = 'Assigned Department';

        $college = self::college($user);
        if ($college && isset($college->name)) {
            $collegeName = $college->name;
        }

        $department = self::department($user);
        if ($department && isset($department->name)) {
            $departmentName = $department->name;
            if (isset($department->place) && $department->place) {
                $departmentName .= ' - ' . $department->place;
            }
        }

        if (self::userHasAnyRole($user, ['admin', 'college_admin', 'director'])) {
            return 'Scope: ' . $collegeName;
        }

        if (self::userHasRole($user, 'department_admin')) {
            return 'Scope: ' . $collegeName . ' / ' . $departmentName;
        }

        if (self::userHasAnyRole($user, ['storekeeper', 'programmer', 'store_incharge', 'd4_seat'])) {
            return 'Functional Scope: ' . $collegeName . ' / ' . $departmentName;
        }

        return 'Scope: Own employee record only';
    }
}