DEE Employee & IT Repair Management System - University Level Scope Update
======================================================================

This build adds university-level role segregation.

Roles
-----
1. superuser
   - University-level user.
   - Can see and manage all colleges, directorates, departments, employees, assets, store stock, indents and repair requests.

2. college_admin
   - College/directorate-level user.
   - Can manage records within his/her college/directorate only.

3. department_admin
   - Department/office/KVK-level user.
   - Can manage records within his/her department only.

4. admin
   - Scoped admin. If linked with a department, works like department admin. If linked only with a college, works like college admin.

5. storekeeper / programmer / d4_seat / director / employee
   - These roles are also scoped through their linked employee college/department.

How scope works
---------------
- Each user has optional college_id and department_id fields.
- Superuser has both blank and sees everything.
- College-level users have college_id set and department_id blank.
- Department-level users have both college_id and department_id set.
- The system also uses the linked employee record as fallback scope.

Filtering added
---------------
- Employee listing: college, department, status filters.
- Repair requests: college, department, status filters.
- Assets: college, department, employee, category, state filters.
- Store stock: college, department, low-stock filters.
- Store indents and dashboard counts are scope-aware.

Employee job type
-----------------
Employee job type is now:
- Permanent
- Adhoc
- Temporary
- Daily Wages

Fresh install commands
----------------------
composer dump-autoload
php artisan migrate:fresh
php artisan db:seed --class=DeeItRepairSeeder
php artisan storage:link
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan serve

Login users
-----------
Superuser            : 9876543200 / password
DEE Department Admin : 9876543210 / password
Storekeeper          : 9876543211 / password
Programmer           : 9876543212 / password
Employee             : 9876543213 / password
Director             : 9876543214 / password
D-4 Seat             : 9876543216 / password

Important
---------
Before copying this version, delete old mixed migration files from database/migrations, then copy this package. This avoids duplicate table/migration errors.
