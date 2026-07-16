DEE Employee & IT Repair Management System - University Level Update

This updated package adds PAU-wide organization support:

1. Colleges / Directorates master table
2. Departments / Offices / KVKs master table
3. Employee current posting: college_id + department_id
4. Employee transfer/posting history table
5. Transfer form on employee detail page
6. Employee filter by college/department/status
7. Asset, repair request, store item and store indent are now department-aware
8. Seed data includes the PAU colleges/directorates and departments list supplied by the user.

Installation for fresh development database:

1. Delete old copied migration files from database/migrations if you have mixed old versions.
2. Copy all folders from this ZIP into the Laravel 5.8 project.
3. Run:

composer dump-autoload
php artisan migrate:fresh
php artisan db:seed --class=DeeItRepairSeeder
php artisan storage:link
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan serve

Login:
Admin       : 9876543210 / password
Storekeeper : 9876543211 / password
Programmer  : 9876543212 / password
Employee    : 9876543213 / password
Director    : 9876543214 / password
D-4 Seat    : 9876543216 / password

Important design note:
The old directorates/sections tables are retained for DEE internal workflow compatibility. For university-wide use, use the new colleges and departments tables as the main posting structure. The label "College / Directorate" is used because the supplied college list includes directorates and offices also.
