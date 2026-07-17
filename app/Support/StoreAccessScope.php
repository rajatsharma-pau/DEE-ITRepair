<?php

namespace App\Support;

class StoreAccessScope
{
    public static function user($user = null)
    {
        return $user ?: auth()->user();
    }

    protected static function normalizeRole($role)
    {
        return strtolower(trim(str_replace(' ', '_', (string) $role)));
    }

    public static function roleNames($user = null)
    {
        $user = self::user($user);
        if (!$user) {
            return array();
        }

        $roles = array();

        if (method_exists($user, 'roleNames')) {
            try {
                $roles = $user->roleNames();
                if ($roles instanceof \Illuminate\Support\Collection) {
                    $roles = $roles->toArray();
                }
            } catch (\Exception $e) {
                $roles = array();
            }
        }

        if (empty($roles) && isset($user->role) && $user->role) {
            $roles[] = $user->role;
        }

        $roles = array_map(function ($role) {
            return StoreAccessScope::normalizeRole($role);
        }, (array) $roles);

        return array_values(array_unique(array_filter($roles)));
    }

    public static function hasExactRole($user, $role)
    {
        $role = self::normalizeRole($role);
        return in_array($role, self::roleNames($user));
    }

    public static function hasExactAnyRole($user, array $roles)
    {
        foreach ($roles as $role) {
            if (self::hasExactRole($user, $role)) {
                return true;
            }
        }
        return false;
    }

    public static function isSuperuser($user = null)
    {
        return self::hasExactRole(self::user($user), 'superuser');
    }

    public static function isStorekeeper($user = null)
    {
        return self::hasExactRole(self::user($user), 'storekeeper');
    }

    public static function isDepartmentAdmin($user = null)
    {
        return self::hasExactRole(self::user($user), 'department_admin');
    }

    public static function isCollegeLevelViewer($user = null)
    {
        $user = self::user($user);
        return self::hasExactAnyRole($user, array('admin', 'college_admin', 'director'));
    }

    public static function canViewStore($user = null)
    {
        $user = self::user($user);

        if (!$user) {
            return false;
        }

        if (self::isSuperuser($user)) {
            return true;
        }

        return self::hasExactAnyRole($user, array(
            'storekeeper',
            'admin',
            'college_admin',
            'department_admin',
            'director',
        ));
    }

    public static function canManageStore($user = null)
    {
        $user = self::user($user);

        if (!$user) {
            return false;
        }

        return self::isSuperuser($user) || self::isStorekeeper($user);
    }

    public static function assertCanViewStore($user = null)
    {
        if (!self::canViewStore($user)) {
            abort(403, 'You are not allowed to view store records.');
        }
    }

    public static function assertCanManageStore($user = null)
    {
        if (!self::canManageStore($user)) {
            abort(403, 'Only Storekeeper or Superuser can add/edit/process store records.');
        }
    }

    public static function collegeId($user = null)
    {
        $user = self::user($user);

        if (!$user) {
            return null;
        }

        if (isset($user->college_id) && $user->college_id) {
            return $user->college_id;
        }

        try {
            $employee = null;

            if (method_exists($user, 'employee')) {
                $employee = $user->employee;
            } elseif (isset($user->employee)) {
                $employee = $user->employee;
            }

            if ($employee && isset($employee->college_id) && $employee->college_id) {
                return $employee->college_id;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    public static function departmentId($user = null)
    {
        $user = self::user($user);

        if (!$user) {
            return null;
        }

        if (isset($user->department_id) && $user->department_id) {
            return $user->department_id;
        }

        try {
            $employee = null;

            if (method_exists($user, 'employee')) {
                $employee = $user->employee;
            } elseif (isset($user->employee)) {
                $employee = $user->employee;
            }

            if ($employee && isset($employee->department_id) && $employee->department_id) {
                return $employee->department_id;
            }
        } catch (\Exception $e) {
            return null;
        }

        return null;
    }

    public static function collegeName($user = null)
    {
        $collegeId = self::collegeId($user);

        if (!$collegeId) {
            return '';
        }

        try {
            $college = \DB::table('colleges')->where('id', $collegeId)->first();
            return $college ? $college->name : '';
        } catch (\Exception $e) {
            return '';
        }
    }

    public static function departmentName($user = null)
    {
        $departmentId = self::departmentId($user);

        if (!$departmentId) {
            return '';
        }

        try {
            $department = \DB::table('departments')->where('id', $departmentId)->first();
            if (!$department) {
                return '';
            }

            $name = $department->name;
            if (isset($department->place) && $department->place) {
                $name .= ' - '.$department->place;
            }

            return $name;
        } catch (\Exception $e) {
            return '';
        }
    }

    public static function applyViewScope($query, $collegeColumn = 'college_id', $departmentColumn = 'department_id', $user = null)
    {
        $user = self::user($user);

        if (!self::canViewStore($user)) {
            return $query->whereRaw('1 = 0');
        }

        if (self::isSuperuser($user)) {
            return $query;
        }

        $collegeId = self::collegeId($user);
        $departmentId = self::departmentId($user);

        // College Admin/Admin/Director can view all store records of their college/directorate.
        // If they also have Storekeeper role, management is still restricted to their own department.
        if (self::isCollegeLevelViewer($user)) {
            if ($collegeId) {
                return $query->where($collegeColumn, $collegeId);
            }
            return $query->whereRaw('1 = 0');
        }

        // Department Admin and Storekeeper view only their own department.
        if (self::isDepartmentAdmin($user) || self::isStorekeeper($user)) {
            if ($departmentId) {
                return $query->where($departmentColumn, $departmentId);
            }
            return $query->whereRaw('1 = 0');
        }

        return $query->whereRaw('1 = 0');
    }

    public static function applyManageScope($query, $collegeColumn = 'college_id', $departmentColumn = 'department_id', $user = null)
    {
        $user = self::user($user);

        if (!self::canManageStore($user)) {
            return $query->whereRaw('1 = 0');
        }

        if (self::isSuperuser($user)) {
            return $query;
        }

        $collegeId = self::collegeId($user);
        $departmentId = self::departmentId($user);

        if ($collegeId && $departmentId) {
            return $query->where($collegeColumn, $collegeId)->where($departmentColumn, $departmentId);
        }

        return $query->whereRaw('1 = 0');
    }

    public static function forceManageScopeData(array $data, $user = null)
    {
        $user = self::user($user);

        self::assertCanManageStore($user);

        if (self::isSuperuser($user)) {
            if (empty($data['college_id']) || empty($data['department_id'])) {
                abort(403, 'College / Directorate and Department / Office / KVK are required.');
            }
            return $data;
        }

        $collegeId = self::collegeId($user);
        $departmentId = self::departmentId($user);

        if (!$collegeId || !$departmentId) {
            abort(403, 'Your login has no college/department assigned. Please update users.college_id and users.department_id.');
        }

        $data['college_id'] = $collegeId;
        $data['department_id'] = $departmentId;

        return $data;
    }

    public static function canViewRow($row, $user = null)
    {
        $user = self::user($user);

        if (!self::canViewStore($user) || !$row) {
            return false;
        }

        if (self::isSuperuser($user)) {
            return true;
        }

        if (self::isCollegeLevelViewer($user)) {
            return isset($row->college_id) && (int) $row->college_id === (int) self::collegeId($user);
        }

        if (self::isDepartmentAdmin($user) || self::isStorekeeper($user)) {
            return isset($row->department_id) && (int) $row->department_id === (int) self::departmentId($user);
        }

        return false;
    }

    public static function canManageRow($row, $user = null)
    {
        $user = self::user($user);

        if (!self::canManageStore($user) || !$row) {
            return false;
        }

        if (self::isSuperuser($user)) {
            return true;
        }

        return isset($row->college_id, $row->department_id)
            && (int) $row->college_id === (int) self::collegeId($user)
            && (int) $row->department_id === (int) self::departmentId($user);
    }

    public static function assertCanViewRow($row, $user = null)
    {
        if (!self::canViewRow($row, $user)) {
            abort(403, 'You are not allowed to view this store record.');
        }
    }

    public static function assertCanManageRow($row, $user = null)
    {
        if (!self::canManageRow($row, $user)) {
            abort(403, 'Only the Storekeeper of this department or Superuser can change this record.');
        }
    }

    public static function canManageEmployee($employee, $user = null)
    {
        $user = self::user($user);

        if (!$employee || !self::canManageStore($user)) {
            return false;
        }

        if (self::isSuperuser($user)) {
            return true;
        }

        return isset($employee->college_id, $employee->department_id)
            && (int) $employee->college_id === (int) self::collegeId($user)
            && (int) $employee->department_id === (int) self::departmentId($user);
    }

    public static function viewColleges($user = null)
    {
        $user = self::user($user);

        if (!\Schema::hasTable('colleges')) {
            return collect();
        }

        $query = \DB::table('colleges');

        if (\Schema::hasColumn('colleges', 'is_active')) {
            $query->where('is_active', 1);
        }

        if (!self::isSuperuser($user) && self::collegeId($user)) {
            $query->where('id', self::collegeId($user));
        }

        return $query->orderBy('name')->get();
    }

    public static function viewDepartments($user = null)
    {
        $user = self::user($user);

        if (!\Schema::hasTable('departments')) {
            return collect();
        }

        $query = \DB::table('departments');

        if (\Schema::hasColumn('departments', 'is_active')) {
            $query->where('is_active', 1);
        }

        if (self::isSuperuser($user)) {
            return $query->orderBy('name')->get();
        }

        if (self::isCollegeLevelViewer($user) && self::collegeId($user)) {
            return $query->where('college_id', self::collegeId($user))->orderBy('name')->get();
        }

        if ((self::isDepartmentAdmin($user) || self::isStorekeeper($user)) && self::departmentId($user)) {
            return $query->where('id', self::departmentId($user))->orderBy('name')->get();
        }

        return collect();
    }
}
