DEE Superuser Role Assignment Final Fix
=======================================

Problem fixed
-------------
As Superuser, role assignment was blocked because EmployeeController was comparing
selected role slugs (example: superuser, admin) with role labels (example: Superuser, Admin).

Files to replace
----------------
1. app/Support/AccessScope.php
2. app/Http/Controllers/EmployeeController.php

Main fixes
----------
- Superuser can assign any role.
- Superuser can select multiple roles such as Superuser + Programmer.
- primaryRoleFrom() keeps Superuser as the primary role when selected with other roles.
- employeesQuery() remains available for assets/forms.
- No legacy directorates table is used in this EmployeeController.
- Department Admin scope and transfer rules remain intact.

After copying files
-------------------
Run from project folder:

composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve

Optional SQL
------------
If your own login accidentally lost the superuser role while testing, open:

snippets/sql/ensure_logged_in_user_is_superuser.sql

Replace YOUR_PHONE_HERE with your phone/login number and run it in phpMyAdmin.
