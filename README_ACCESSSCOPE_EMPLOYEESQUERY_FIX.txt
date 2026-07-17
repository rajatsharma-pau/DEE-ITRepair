DEE AccessScope employeesQuery() Fix Patch
==========================================

Error fixed:
Call to undefined method App\Support\AccessScope::employeesQuery()

Why it happened:
AssetController uses AccessScope::employeesQuery() to populate employee dropdowns on the assets page, but the method was missing from AccessScope.php.

Files to replace:
- app/Support/AccessScope.php

After copying, run:
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

Rules applied by employeesQuery():
- Superuser: all employees
- Admin / College Admin / Director: own college/directorate employees
- Department Admin: own department employees
- Employee-only: own employee record only
- Storekeeper / Programmer / Store Incharge / D-4 Seat: own department employees
