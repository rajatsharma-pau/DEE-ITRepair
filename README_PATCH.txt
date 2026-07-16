Patch: Remove unnecessary proforma/manual sanction fields from Storekeeper action screen

Replace these files in your Laravel project:
- app/Http/Controllers/RepairRequestController.php
- resources/views/repair_requests/show.blade.php
- resources/views/repair_requests/proforma.blade.php

Then run:
composer dump-autoload
php artisan view:clear
php artisan cache:clear
php artisan route:clear

No migration is required for this patch.
