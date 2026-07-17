<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Schema;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'login_id',
        'password',
        'role',
        'college_id',
        'department_id',
        'is_active',
        'must_change_password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
        'must_change_password' => 'boolean',
    ];

    public function employee()
    {
        return $this->hasOne(Employee::class);
    }

    public function college()
    {
        return $this->belongsTo(College::class);
    }

    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'user_roles')
            ->withPivot(['college_id', 'department_id', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * Return all role names/slugs assigned to the user.
     * This supports both new multi-role system and old users.role column.
     */
    public function roleNames()
    {
        $names = [];

        // Old single role column fallback
        if (!empty($this->role)) {
            $names[] = $this->role;
        }

        // New multi-role tables fallback-safe
        try {
            if (Schema::hasTable('roles') && Schema::hasTable('user_roles')) {
                $dbRoles = $this->roles()
                    ->where(function ($q) {
                        $q->whereNull('roles.is_active')->orWhere('roles.is_active', 1);
                    })
                    ->get();

                foreach ($dbRoles as $role) {
                    if (!empty($role->name)) {
                        $names[] = $role->name;
                    }
                    if (!empty($role->slug)) {
                        $names[] = $role->slug;
                    }
                    if (!empty($role->display_name)) {
                        $names[] = $role->display_name;
                    }
                }
            }
        } catch (\Exception $e) {
            // During migration/seed/old installations, silently use users.role only.
        }

        $names = array_map(function ($r) {
            return strtolower(trim(str_replace(' ', '_', $r)));
        }, $names);

        return array_values(array_unique(array_filter($names)));
    }

    public function roleLabel()
    {
        return collect($this->roleNames())->map(function ($role) {
            return ucwords(str_replace('_', ' ', $role));
        })->implode(', ');
    }

    public function isSuperUser()
    {
        return $this->hasRole('superuser');
    }

    public function hasRole($roles)
    {
        $roles = is_array($roles) ? $roles : func_get_args();

        $roles = array_map(function ($r) {
            return strtolower(trim(str_replace(' ', '_', $r)));
        }, $roles);

        $userRoles = $this->roleNames();

        if (in_array('superuser', $userRoles)) {
            return true;
        }

        return count(array_intersect($roles, $userRoles)) > 0;
    }

    public function hasAnyRole($roles)
    {
        $roles = is_array($roles) ? $roles : func_get_args();
        return $this->hasRole($roles);
    }

    public function isRole($roles)
    {
        $roles = is_array($roles) ? $roles : func_get_args();
        return $this->hasRole($roles);
    }

    public function assignRole($roleName, $collegeId = null, $departmentId = null, $isPrimary = false)
    {
        $roleName = strtolower(trim(str_replace(' ', '_', $roleName)));

        $role = Role::firstOrCreate(
            ['name' => $roleName],
            ['display_name' => ucwords(str_replace('_', ' ', $roleName)), 'is_active' => 1]
        );

        $this->roles()->syncWithoutDetaching([
            $role->id => [
                'college_id' => $collegeId,
                'department_id' => $departmentId,
                'is_primary' => $isPrimary ? 1 : 0,
            ]
        ]);

        if ($isPrimary) {
            $this->role = $roleName;
            if (Schema::hasColumn('users', 'college_id')) {
                $this->college_id = $collegeId;
            }
            if (Schema::hasColumn('users', 'department_id')) {
                $this->department_id = $departmentId;
            }
            $this->save();
        }

        return $this;
    }

    public function syncRoleNames(array $roleNames, $collegeId = null, $departmentId = null)
    {
        $roleNames = array_values(array_unique(array_filter(array_map(function ($r) {
            return strtolower(trim(str_replace(' ', '_', $r)));
        }, $roleNames))));

        if (empty($roleNames)) {
            $roleNames = ['employee'];
        }

        $sync = [];
        foreach ($roleNames as $index => $roleName) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['display_name' => ucwords(str_replace('_', ' ', $roleName)), 'is_active' => 1]
            );

            $sync[$role->id] = [
                'college_id' => $collegeId,
                'department_id' => $departmentId,
                'is_primary' => $index === 0 ? 1 : 0,
            ];
        }

        $this->roles()->sync($sync);
        $this->role = $roleNames[0];

        if (Schema::hasColumn('users', 'college_id')) {
            $this->college_id = $collegeId;
        }
        if (Schema::hasColumn('users', 'department_id')) {
            $this->department_id = $departmentId;
        }

        $this->save();

        return $this;
    }

    public function getPhotoUrlAttribute()
    {
        if ($this->employee && !empty($this->employee->photo)) {
            return asset('storage/' . $this->employee->photo);
        }

        if ($this->employee && !empty($this->employee->profile_picture)) {
            return asset('storage/' . $this->employee->profile_picture);
        }

        return asset('images/default-user.png');
    }

    public function hasActiveCharge($chargeName)
{
    $employee = null;

    try {
        $employee = $this->employee;
    } catch (\Exception $e) {
        $employee = null;
    }

    if (!$employee || !method_exists($employee, 'hasActiveCharge')) {
        return false;
    }

    return $employee->hasActiveCharge($chargeName);
}

}
