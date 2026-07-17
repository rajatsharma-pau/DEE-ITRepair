DEE Department Admin + Employee Scope Patch
==========================================

Problem fixed
-------------
A user having roles like "Department Admin, Employee" was showing:

    Scope: Assigned College / Directorate / Assigned Department

and was not able to see department employees.

Main reason
-----------
AccessScope was reading scope only from users.college_id and users.department_id.
If those columns were blank, the Department Admin became scope-less and employee list became empty.

What this patch does
--------------------
1. AccessScope now resolves college/department from:
   - users.college_id / users.department_id
   - employee posting linked with the login
   - user_roles pivot scope

2. Employee + Department Admin is no longer treated as employee-only.
   Department Admin permission wins.

3. Department Admin can:
   - see only own department employees
   - add employees only in own department
   - edit employees in own department
   - add promotion/service movement for own department employees
   - transfer own department employee to any college/directorate/department

4. EmployeeController now forces own department/college scope during add/edit for Department Admin.

5. Employee show page now uses all colleges/departments for transfer destination.

Files to replace
----------------
app/Support/AccessScope.php
app/Http/Controllers/EmployeeController.php
resources/views/employees/show.blade.php

Optional database cleanup
-------------------------
If old users still have blank users.college_id or users.department_id, run:

snippets/sql/sync_user_scope_from_employee.sql

Commands after copying
----------------------
cd "E:\PAU Mobile Project\dee-it-repair"
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve
