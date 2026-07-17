DEE IT Repair Patch: Change Password, Forgot Password OTP, Employee Search, Clean Name

1) Copy all folders from this patch into your Laravel project.

2) Add this line at the bottom of routes/web.php:

require __DIR__.'/dee_password_routes.php';

3) Add middleware alias in app/Http/Kernel.php if you want to force default password change:

'force.password.change' => \App\Http\Middleware\ForcePasswordChange::class,

Optional: apply it to authenticated route groups. If you only want manual Change Password menu, this is not mandatory.

4) Add menu links in resources/views/layouts/app.blade.php:

For logged-in user menu:
<a class="dropdown-item" href="{{ route('password.change') }}">Change Password</a>

On login page:
<a href="{{ route('password.phone.form') }}">Forgot Password?</a>

5) OTP SMS API integration:
Edit app/Services/OtpSmsService.php and replace the TODO block with your actual SMS API.
For testing, OTP is currently written into laravel.log.

6) Employee search:
- Open app/Http/Controllers/EmployeeController.php
- In index(), before paginate(), add code from:
  snippets/EmployeeController_index_search_snippet.php
- Open resources/views/employees/index.blade.php
- Add the search box snippet from:
  snippets/employee_index_search_box.blade.php

7) Remove duplicate Dr. Dr. in listing:
Use this in employee listing/show pages:

{{ \App\Support\EmployeeFormatter::displayName($employee) }}

This converts "Dr. Dr. Amit Salaria" to "Dr. Amit Salaria".

8) Run commands:
composer dump-autoload
php artisan migrate
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve
