Final scope rules implemented by this patch
===========================================

1) Superuser
- Can see/manage everything.
- Can assign any role.
- Can transfer any employee anywhere.

2) College Admin / Admin / Director
- Can see/edit/manage employees of their own college/directorate only.
- Can assign limited roles to employees of their own college/directorate.
- Can transfer employees FROM their own college/directorate TO any college/directorate/department.

Allowed roles for College Admin / Admin / Director:
- employee
- department_admin
- storekeeper
- programmer
- store_incharge
- d4_seat
- director

They cannot assign:
- superuser
- admin
- college_admin

3) Department Admin
- Can see/edit only own department employees.
- Can assign limited roles only to employees of own department.
- Can transfer employees FROM own department TO any department under any college/directorate.

Allowed roles for Department Admin:
- employee
- storekeeper
- programmer
- store_incharge

Installation
============

Copy files to project root preserving paths:
- app/Support/AccessScope.php
- app/Http/Controllers/EmployeeTransferController.php

Apply snippets manually:
- snippets/EmployeeController_role_assignment_update.php
- snippets/employee_role_multiselect_blade.php
- snippets/routes_transfer.php

Run:
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve

Important
=========

The transfer controller restricts the SOURCE employee scope only.
Destination is university-wide for Superuser, College/Admin/Director, and Department Admin as requested.
