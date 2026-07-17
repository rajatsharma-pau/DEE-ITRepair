DEE Employee Search Patch
=========================

This patch adds search on the Employees listing page.

Search supports:
- Employee name: full_name, first_name, middle_name, last_name
- Phone number from employees table
- Phone/name/email/login_id from users table
- Employee code
- GPF No.
- NPS No.
- PAN No.
- Designation name

Existing filters remain intact:
- College / Directorate
- Department / Office / KVK
- Status

Files included:
- app/Http/Controllers/EmployeeController.php
- resources/views/employees/index.blade.php

After copying, run:
composer dump-autoload
php artisan view:clear
php artisan cache:clear
php artisan route:clear
php artisan serve

No migration is required.
