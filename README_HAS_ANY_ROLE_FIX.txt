Fix for error:
BadMethodCallException: Call to undefined method App\User::hasAnyRole()

Files included:
1. app/User.php
2. app/Http/Middleware/RoleMiddleware.php

Copy these files into your Laravel 5.8 project and replace existing files.

Then run:
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

If still showing old error, delete compiled views:
del /q storage\framework\views\*.php

Then restart:
php artisan serve

This User.php supports:
- users.role old single role
- roles + user_roles multi-role system
- hasRole()
- hasAnyRole()
- isRole()
- isSuperUser()
- roleLabel()
- employee profile photo in navbar
