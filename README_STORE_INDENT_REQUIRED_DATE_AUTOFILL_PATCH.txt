DEE Store Indent Required Date Auto-fill Patch
================================================

Purpose
-------
This patch fixes /store-indents/create so the Required Date field is auto-filled with today's date and marked mandatory with a red star.

Files included
--------------
app/Http/Controllers/StoreIndentController.php
resources/views/store_indents/create.blade.php

What changed
------------
1. StoreIndentController@create now sends:
   $requiredDate = date('Y-m-d');

2. resources/views/store_indents/create.blade.php now uses:
   old('required_date', $requiredDate)

3. StoreIndentController@store validates required_date as required date, but also has a backend fallback to today.

How to apply
------------
Copy the files into your Laravel project and replace existing files.

Run:
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve

Expected result
---------------
When any employee opens:
http://localhost:8000/store-indents/create

Required Date will show today's date automatically.
