PATCH: Master menu links not coming

1) Copy routes/dee_master_routes.php into your Laravel project's routes folder.

2) Open routes/web.php and add this line at the very bottom:

require __DIR__.'/dee_master_routes.php';

3) Copy resources/views/layouts/master_menu_links.blade.php into:
resources/views/layouts/master_menu_links.blade.php

4) Open resources/views/layouts/app.blade.php.
Find the Admin dropdown menu and add this line inside that dropdown:

@include('layouts.master_menu_links')

Example:

<div class="dropdown-menu dropdown-menu-right">
    <a class="dropdown-item" href="{{ route('employees.index') }}">Employees</a>
    <a class="dropdown-item" href="{{ route('assets.index') }}">Assets</a>
    @include('layouts.master_menu_links')
</div>

5) Run:

composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan route:list | findstr master
php artisan serve

6) IMPORTANT: Menu is visible only to a user having role superuser.
Check the logged in user has superuser role in user_roles table.

If you are using old single-role column also, check users.role = superuser.
