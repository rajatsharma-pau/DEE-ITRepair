Repair Request Create Blade Fix
==============================

Replace:
  resources/views/repair_requests/create.blade.php

Main fixes:
- Employee-only login now sends employee_id as hidden input with name="employee_id".
- Superuser/Admin/College Admin/Department Admin/Director/Storekeeper can select employee.
- Mandatory fields show red *.
- Old values are preserved after validation errors.
- For admin/storekeeper employee selection, allocated assets reload and selected old asset is restored.
- Category, default problem, room, description and attachment UI improved.

After copying, run:
  composer dump-autoload
  php artisan config:clear
  php artisan cache:clear
  php artisan route:clear
  php artisan view:clear
