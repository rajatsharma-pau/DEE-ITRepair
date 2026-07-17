DEE Employee Show UI + Mandatory Fields Patch
============================================

Page:
http://localhost:8000/employees/{id}
Example:
http://localhost:8000/employees/38

Replace this file:
resources/views/employees/show.blade.php

What changed:
- Cleaner employee profile UI.
- Summary cards for phone, DOJ, retirement.
- Role badges shown cleanly.
- Better profile/photo area.
- Required fields show red *.
- Transfer form defaults Transfer Date to today's date.
- Service Movement form defaults Effective Date to today's date.
- Additional Charge form defaults From Date to today's date.
- File inputs now accept PDF/JPG/PNG.
- Empty histories show proper messages.
- Department Admin / College Admin / Superuser action visibility uses AccessScope when available.
- No functional table/database changes.

Mandatory client-side fields:
- Transfer: To College, To Department, Transfer Date.
- Service Movement: Type, Effective Date.
- Promotion/Reversion: To Designation OR Manual To Designation required by client-side check.
- Additional Charge: Charge Name, From Date.

After copying files, run:
cd "E:\PAU Mobile Project\dee-it-repair"
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve

Note:
This patch is UI-focused. Backend validation should remain in your controller/request validation for security.
