Employee Search login_id Fix

Issue:
SQLSTATE[42S22]: Unknown column 'login_id' in where clause.

Reason:
Your current system is phone-number login only, so the users table may not have login_id.

Fix:
EmployeeController search now checks Schema::hasColumn('users', 'login_id') before searching login_id.
It also checks employee search columns before using them, so the search is safer across different database versions.

Apply:
1. Copy app/Http/Controllers/EmployeeController.php
2. Run:
   composer dump-autoload
   php artisan view:clear
   php artisan cache:clear
   php artisan route:clear
