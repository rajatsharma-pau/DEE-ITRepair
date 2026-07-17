DEE AccessScope roleOptions() Fix Patch
=======================================

This patch fixes:

Call to undefined method App\Support\AccessScope::roleOptions()

Files included:
- app/Support/AccessScope.php

What was added:
- AccessScope::roleOptions($employee = null, $user = null)

It returns assignable roles based on your final rules:
- Superuser: all roles
- Admin / College Admin / Director: limited roles for employees in own college/directorate
- Department Admin: limited roles for employees in own department

Apply:
1. Replace app/Support/AccessScope.php
2. Run:
   composer dump-autoload
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   php artisan serve
