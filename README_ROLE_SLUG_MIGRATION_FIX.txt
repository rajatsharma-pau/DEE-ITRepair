Fix for:
SQLSTATE[HY000]: General error: 1364 Field 'display_name' doesn't have a default value
while running migration:
2026_06_16_000004_add_slug_to_roles_table

Copy this file into your project and replace the existing migration:
database/migrations/2026_06_16_000004_add_slug_to_roles_table.php

Then run:
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
