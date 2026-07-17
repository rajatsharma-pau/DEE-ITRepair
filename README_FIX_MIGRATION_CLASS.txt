Fix for:
Symfony\Component\Debug\Exception\FatalThrowableError: Class 'ExpandEmployeesForDee46Import' not found

Reason:
Laravel resolves the class name from this migration filename:
2026_06_16_000003_expand_employees_for_dee_46_import.php
So the class inside the file must be exactly:
ExpandEmployeesForDee46Import

How to apply:
1. Replace the existing file:
   database/migrations/2026_06_16_000003_expand_employees_for_dee_46_import.php
   with the file in this patch.

2. Run:
   composer dump-autoload
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan migrate
   php artisan db:seed --class=Dee46EmployeesPhoneLoginSeeder

If this migration already partially ran, use migrate:fresh only on your development database.
