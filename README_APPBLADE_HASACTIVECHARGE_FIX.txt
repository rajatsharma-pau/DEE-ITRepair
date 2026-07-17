DEE app.blade.php hasActiveCharge Fix
=====================================

Error fixed:
    Call to undefined method App\User::hasActiveCharge()

Cause:
    resources/views/layouts/app.blade.php was calling hasActiveCharge() on Auth::user().
    That method exists on Employee, not User.

Main replacement file:
    resources/views/layouts/app.blade.php

This file also keeps the final queue visibility rules:
    - Programmer sees Verification Pending only.
    - Programmer does not see Pending with Storekeeper unless also Storekeeper or Superuser.
    - Storekeeper sees Pending with Storekeeper.
    - D-4 Seat sees D-4 Manual Files.
    - Superuser sees all handler links and all repair requests on normal /repair-requests page.

After copying the file, run:

    cd "E:\PAU Mobile Project\dee-it-repair"
    composer dump-autoload
    php artisan config:clear
    php artisan cache:clear
    php artisan route:clear
    php artisan view:clear
    php artisan serve

Optional:
    snippets/optional_User_model_helper.txt contains a safety method for app/User.php if any old file still calls $user->hasActiveCharge().
