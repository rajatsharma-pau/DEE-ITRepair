DEE Master Edit/Delete Superuser Patch
====================================

Purpose
-------
This patch adds Superuser-only master management screens for:
1. College / Directorate
2. Department / Office / KVK
3. Designation

Rule Implemented
----------------
Only Superuser can add/edit/delete/deactivate these masters.

If a master record is already used anywhere, it will NOT be hard deleted.
Instead:
- If the table has is_active column, it will be marked inactive.
- User can still edit/rename the record.

If a master record is not used anywhere, it can be deleted.

Files Included
--------------
app/Http/Controllers/Masters/MasterBaseController.php
app/Http/Controllers/Masters/CollegeMasterController.php
app/Http/Controllers/Masters/DepartmentMasterController.php
app/Http/Controllers/Masters/DesignationMasterController.php
routes/dee_master_routes.php
database/migrations/2026_07_17_000020_ensure_master_active_columns.php
resources/views/masters/...

Installation
------------
1. Copy all files into your Laravel project.

2. Add this line at the end of routes/web.php:

require __DIR__.'/dee_master_routes.php';

3. Run:

composer dump-autoload
php artisan migrate
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

4. Open URLs as Superuser:

/master/colleges
/master/departments
/master/designations

Navbar Link Suggestion
----------------------
Add these links inside Admin/Masters dropdown in layouts/app.blade.php:

<a class="dropdown-item" href="{{ route('master.colleges.index') }}">Colleges / Directorates</a>
<a class="dropdown-item" href="{{ route('master.departments.index') }}">Departments / Offices / KVKs</a>
<a class="dropdown-item" href="{{ route('master.designations.index') }}">Designations</a>

Important
---------
This patch uses middleware: role:superuser
So your RoleMiddleware and User::hasAnyRole() should already be working.
