DEE master-only seeder with designation integration
====================================================

Files included:
- database/seeds/DeeItRepairSeeder.php

What changed:
1. Removed dummy users, dummy employees, dummy vendors, dummy assets and dummy store stock.
2. Keeps only master data:
   - roles
   - colleges/directorates
   - departments/KVKs/offices
   - Directorate of Extension Education department
   - sections
   - country/state/city
   - repair categories
   - repair routing rules
   - default problem templates
   - designations
3. Integrated designations from the uploaded lists:
   - 224 unique Scientific designations from designations.csv
   - 107 unique Administrative designations from the pasted staff designation list
   - 331 total unique designations
4. Duplicate prevention:
   - trims names
   - collapses double spaces
   - compares case-insensitively
   - updates existing designation instead of inserting duplicate

How to use:
1. Replace database/seeds/DeeItRepairSeeder.php with this patch file.
2. Run:

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

Note:
This seeder does not create login users. Real users should come from Dee46EmployeesPhoneLoginSeeder or another real user seeder.
