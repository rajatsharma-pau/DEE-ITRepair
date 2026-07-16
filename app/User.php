<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name', 'phone', 'email', 'password', 'role', 'college_id', 'department_id', 'is_active', 'must_change_password'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
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

    public function roleNames()
    {
        $names = $this->roles()->where('roles.is_active', 1)->pluck('roles.name')->toArray();

        // Backward compatibility: existing code/table still has users.role as primary role.
        if ($this->role && !in_array($this->role, $names)) {
            $names[] = $this->role;
        }

        return array_values(array_unique($names));
    }

    public function roleLabel()
    {
        $names = $this->roleNames();
        return collect($names)->map(function ($r) {
            return ucwords(str_replace('_', ' ', $r));
        })->implode(', ');
    }

    public function isSuperUser()
    {
        return $this->hasRole('superuser');
    }

    public function hasRole($roles)
    {
        $roles = is_array($roles) ? $roles : func_get_args();
        if (in_array('superuser', $this->roleNames())) {
            return true;
        }
        return count(array_intersect($roles, $this->roleNames())) > 0;
    }

    public function hasAnyRole($roles)
    {
        return $this->hasRole(is_array($roles) ? $roles : func_get_args());
    }

    public function isRole($roles)
    {
        return $this->hasRole(is_array($roles) ? $roles : func_get_args());
    }

    public function assignRole($roleName, $collegeId = null, $departmentId = null, $isPrimary = false)
    {
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
            $this->roles()->updateExistingPivot($role->id, ['is_primary' => 1]);
            $this->role = $roleName;
            $this->save();
        }

        return $this;
    }

    public function syncRoleNames(array $roleNames, $collegeId = null, $departmentId = null)
    {
        $roleNames = array_values(array_unique(array_filter($roleNames)));
        if (empty($roleNames)) {
            $roleNames = ['employee'];
        }

        $sync = [];
        foreach ($roleNames as $i => $roleName) {
            $role = Role::firstOrCreate(
                ['name' => $roleName],
                ['display_name' => ucwords(str_replace('_', ' ', $roleName)), 'is_active' => 1]
            );
            $sync[$role->id] = [
                'college_id' => $collegeId,
                'department_id' => $departmentId,
                'is_primary' => $i === 0 ? 1 : 0,
            ];
        }

        $this->roles()->sync($sync);
        $this->role = $roleNames[0]; // primary/default role for older code and reports
        $this->college_id = $collegeId;
        $this->department_id = $departmentId;
        $this->save();

        return $this;
    }
}
