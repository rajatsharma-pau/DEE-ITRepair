Fix for SQL error:
Unknown column 'department_id' in 'where clause' while querying departments table.

Cause:
AccessScope::apply() was being used on Department::query() with default column 'department_id'.
The departments table has primary key 'id' and foreign key 'college_id', not 'department_id'.

Files changed:
- app/Support/AccessScope.php
- app/Http/Controllers/MasterDataController.php

After copying, run:
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
