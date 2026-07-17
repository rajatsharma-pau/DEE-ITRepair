DEE Role Display Name Seeder Fix Patch
=======================================

Files included:
- app/Role.php
- database/seeds/DeeItRepairSeeder.php

What this fixes:
1. Fixes SQL error: Field 'display_name' doesn't have a default value.
2. Ensures every role insert includes name, slug, display_name, description, is_active.
3. Updates DEE seed mapping from Communication Centre to Directorate of Extension Education.
4. Makes Role model fillable for display_name and slug.

Install:
1. Copy the folders from this patch into the Laravel project root.
2. Replace existing files when asked.
3. Run:

composer dump-autoload
php artisan migrate:fresh
php artisan db:seed --class=DeeItRepairSeeder
php artisan db:seed --class=Dee46EmployeesPhoneLoginSeeder
php artisan storage:link
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve

If using Windows and old compiled views are still causing errors, run:
del /q storage\framework\views\*.php
