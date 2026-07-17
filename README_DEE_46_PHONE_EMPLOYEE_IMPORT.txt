DEE 46 Employees - Phone Login + Employee Seeder Patch
============================================================

Source sheet:
DEE_46_Employees_Reanalysed_Complete(2).xlsx

Validation
----------
Total records found: 46
Missing phone numbers: 0
Duplicate phone numbers: 0
All 46 employees can be created with mobile-number login.

What this patch does
--------------------
1. Uses phone number as the login field.
2. Creates/updates users for all 46 employees.
3. Assigns the employee role.
4. Imports employee details from the sheet into employees table.
5. Adds missing employee DB fields required by the sheet.
6. Creates/fetches designations from master table.
7. Maps college/department from the sheet by default.
8. Also stores promotion history JSON and service movement entries where the service movement table exists.

Files included
--------------
database/migrations/2026_06_16_000002_ensure_phone_login_for_dee_employees.php
database/migrations/2026_06_16_000003_expand_employees_for_dee_46_import.php
database/seeds/Dee46EmployeesPhoneLoginSeeder.php
app/Http/Controllers/Auth/LoginController.php
app/User.php
resources/views/auth/login.blade.php
DEE_46_Phone_Login_List.csv

Install
-------
Copy the patch folders into your Laravel 5.8 project and run:

composer dump-autoload
php artisan migrate
php artisan db:seed --class=Dee46EmployeesPhoneLoginSeeder
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve

Login
-----
Mobile Number: employee mobile number from the Excel sheet
Password: password

Example:
Mobile Number: 9417188183
Password: password

Department mapping
------------------
The seeder currently has:

protected $useSheetDepartmentIds = true;

This means it uses Parent OU / Department IDs from the sheet if those records exist in colleges/departments tables.

If you want all 46 employees to be forced under:
Directorate of Extension Education / Directorate of Extension Education

then edit the seeder and set:

protected $useSheetDepartmentIds = false;

Security note
-------------
The sheet contains PAN and other sensitive employee information. Do not show PAN/Aadhaar openly to all roles. Only superuser/authorized admins should see full values.
