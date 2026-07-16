DEE Employee & IT Repair Management System - Laravel 5.8

This package is a clean full module with updated employee fields:
- salutation, first/middle/last name
- GPF, NPS, PAN, Aadhaar, salary account number
- job type: Regular / Contract / Daily Wages
- designation master dropdown + manual designation field
- DOB, DOJ, last promotion date, retirement date, annual increment date
- address line 1, line 2, city, state, country, zip
- optional employee photo upload and display

Fresh installation steps:
1. Create fresh Laravel 5.8 project.
2. Copy these folders/files into the project root.
3. In app/Http/Kernel.php confirm this route middleware exists:
   'role' => \App\Http\Middleware\RoleMiddleware::class,
4. Configure .env database.
5. Run:
   composer dump-autoload
   php artisan migrate:fresh
   php artisan db:seed --class=DeeItRepairSeeder
   php artisan storage:link
   php artisan serve

Default logins:
Admin       9876543210 / password
Storekeeper 9876543211 / password
Programmer  9876543212 / password
Employee    9876543213 / password

Important:
- This is for a fresh database. migrate:fresh will drop all tables.
- If you already have live data, do not run migrate:fresh. Create an ALTER migration instead.
- Photo files are stored in storage/app/public/employee_photos and served through public/storage.

Retirement calculation:
- Auto calculated as the last day of the birth month after completing 60 years.
- User can manually edit the retirement date.

Annual increment calculation:
- If last promotion date exists, increment date is one year after last promotion date.
- Otherwise, it is one year after date of joining.
- User can manually edit the increment date.
