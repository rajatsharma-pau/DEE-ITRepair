<?php

namespace App\Support;

use App\College;
use App\Department;
use App\Employee;
use Illuminate\Support\Facades\Auth;

class AccessScope
{
    public static function user()
    {
        return Auth::user();
    }

    public static function hasRole($roles, $user = null)
    {
        $user = $user ?: self::user();
        if (!$user) return false;
        $roles = is_array($roles) ? $roles : func_get_args();
        // If second argument was passed through func_get_args, clean it.
        $roles = array_filter($roles, function($v){ return is_string($v); });
        return $user->hasAnyRole($roles);
    }

    public static function isSuperUser($user = null)
    {
        $user = $user ?: self::user();
        return $user && $user->hasRole('superuser');
    }

    public static function collegeId($user = null)
    {
        $user = $user ?: self::user();
        if (!$user) return null;
        if (!empty($user->college_id)) return $user->college_id;
        return optional($user->employee)->college_id;
    }

    public static function departmentId($user = null)
    {
        $user = $user ?: self::user();
        if (!$user) return null;
        if (!empty($user->department_id)) return $user->department_id;
        return optional($user->employee)->department_id;
    }

    public static function hasAdminPower($user = null)
    {
        $user = $user ?: self::user();
        return $user && $user->hasAnyRole(['superuser','admin','college_admin','department_admin','director']);
    }

    public static function hasCollegePower($user = null)
    {
        $user = $user ?: self::user();
        return $user && $user->hasAnyRole(['admin','college_admin','director']);
    }

    public static function hasDepartmentPower($user = null)
    {
        $user = $user ?: self::user();
        return $user && $user->hasAnyRole(['admin','department_admin','storekeeper','programmer','d4_seat']);
    }

    public static function isDepartmentScoped($user = null)
    {
        $user = $user ?: self::user();
        if (!$user || self::isSuperUser($user)) return false;
        return self::hasDepartmentPower($user) && self::departmentId($user);
    }

    public static function isCollegeScoped($user = null)
    {
        $user = $user ?: self::user();
        if (!$user || self::isSuperUser($user)) return false;
        if (self::isDepartmentScoped($user)) return false;
        return self::hasCollegePower($user) && self::collegeId($user);
    }

    public static function isEmployeeOnly($user = null)
    {
        $user = $user ?: self::user();
        if (!$user) return false;
        return $user->hasRole('employee') && !$user->hasAnyRole(['superuser','admin','college_admin','department_admin','director','storekeeper','programmer','d4_seat']);
    }

    public static function scopeLabel($user = null)
    {
        $user = $user ?: self::user();
        if (!$user) return 'No scope';
        if (self::isSuperUser($user)) return 'University Level';
        if (self::isDepartmentScoped($user)) {
            $dept = Department::find(self::departmentId($user));
            return 'Department: '.optional($dept)->name;
        }
        if (self::isCollegeScoped($user)) {
            $college = College::find(self::collegeId($user));
            return 'College/Directorate: '.optional($college)->name;
        }
        return 'Own Records';
    }

    public static function apply($query, $collegeColumn = 'college_id', $departmentColumn = 'department_id', $user = null)
    {
        $user = $user ?: self::user();
        if (!$user || self::isSuperUser($user)) return $query;

        // When applying scope directly on master tables, their own PK columns must be used.
        // Example: departments table has id + college_id, it does NOT have department_id.
        try {
            $table = method_exists($query, 'getModel') ? $query->getModel()->getTable() : null;
            if ($table === 'departments') {
                $collegeColumn = 'college_id';
                $departmentColumn = 'id';
            } elseif ($table === 'colleges') {
                $collegeColumn = 'id';
                $departmentColumn = null;
            }
        } catch (\Exception $e) {
            // Keep default columns if builder/model table cannot be detected.
        }

        if (self::isDepartmentScoped($user) && $departmentColumn) {
            return $query->where($departmentColumn, self::departmentId($user));
        }

        if (self::isCollegeScoped($user) && $collegeColumn) {
            return $query->where($collegeColumn, self::collegeId($user));
        }

        return $query;
    }

    public static function canAccessCollege($collegeId, $user = null)
    {
        $user = $user ?: self::user();
        if (!$user || !$collegeId) return true;
        if (self::isSuperUser($user)) return true;
        if (self::collegeId($user) && (int) self::collegeId($user) === (int) $collegeId) return true;
        return false;
    }

    public static function canAccessDepartment($departmentId, $user = null)
    {
        $user = $user ?: self::user();
        if (!$user || !$departmentId) return true;
        if (self::isSuperUser($user)) return true;
        $dept = Department::find($departmentId);
        if (!$dept) return false;
        if (self::isDepartmentScoped($user)) return (int) self::departmentId($user) === (int) $departmentId;
        if (self::isCollegeScoped($user)) return (int) self::collegeId($user) === (int) $dept->college_id;
        return false;
    }

    public static function canAccessEmployee(Employee $employee, $user = null)
    {
        $user = $user ?: self::user();
        if (!$user) return false;
        if (self::isSuperUser($user)) return true;
        if (self::hasAdminPower($user)) {
            if (self::isDepartmentScoped($user)) return (int) self::departmentId($user) === (int) $employee->department_id;
            if (self::isCollegeScoped($user)) return (int) self::collegeId($user) === (int) $employee->college_id;
            return true;
        }
        if ($user->hasAnyRole(['storekeeper','programmer','d4_seat'])) {
            if (self::isDepartmentScoped($user)) return (int) self::departmentId($user) === (int) $employee->department_id;
            if (self::isCollegeScoped($user)) return (int) self::collegeId($user) === (int) $employee->college_id;
        }
        if ($user->hasRole('employee')) return optional($user->employee)->id === $employee->id;
        return false;
    }

    public static function employeesQuery()
    {
        return self::apply(Employee::query());
    }

    public static function colleges()
    {
        $user = self::user();
        $q = College::where('is_active', 1)->orderBy('name');
        if ($user && !self::isSuperUser($user) && self::collegeId($user)) {
            $q->where('id', self::collegeId($user));
        }
        return $q->get();
    }

    public static function departments()
    {
        $user = self::user();
        $q = Department::where('is_active', 1)->orderBy('name');
        if ($user && !self::isSuperUser($user)) {
            if (self::isDepartmentScoped($user)) $q->where('id', self::departmentId($user));
            elseif (self::collegeId($user)) $q->where('college_id', self::collegeId($user));
        }
        return $q->get();
    }

    public static function roleOptions()
    {
        $user = self::user();
        $all = ['employee','storekeeper','programmer','d4_seat','director','department_admin','college_admin','admin','superuser'];
        if (!$user || self::isSuperUser($user)) return $all;
        if ($user->hasAnyRole(['admin','college_admin','director'])) return ['employee','storekeeper','programmer','d4_seat','department_admin'];
        if ($user->hasRole('department_admin')) return ['employee','storekeeper','programmer','d4_seat'];
        return ['employee'];
    }

    public static function primaryRoleFrom(array $roles)
    {
        $priority = ['superuser','admin','college_admin','department_admin','director','storekeeper','programmer','d4_seat','employee'];
        foreach ($priority as $role) {
            if (in_array($role, $roles)) return $role;
        }
        return 'employee';
    }
}
