DEE Final Store/Asset/Indent Scope Patch
======================================

Apply this patch over your current Laravel 5.8 project.

Final rules implemented
-----------------------
1. Superuser
   - Full access to stock, assets, and indents.

2. Storekeeper
   - Can add/edit stock only for own department.
   - Can add/edit assets only for own department.
   - Can issue/reject indents only for own department.
   - College / Directorate and Department / Office / KVK auto-populate from users.college_id and users.department_id.
   - If users scope is missing, the form shows a warning.

3. Department Admin
   - Can view stock/assets/indents of own department.
   - Cannot add/edit stock.
   - Cannot add/edit assets.
   - Cannot issue/reject indents.

4. College Admin / Admin / Director
   - Can view store records as per own college/directorate.
   - Cannot manage store unless also assigned Storekeeper role.

5. Employee
   - Can see own allocated assets.
   - Can submit own indent only when role is normal employee.

Important field meanings
------------------------
Brand: Company/make of the item. Example: HP, Dell, Camlin. Optional.
Opening Stock: Quantity available when creating the item for the first time.
Reorder Level: Minimum alert level. Example: if reorder level is 10, item shows Low Stock when current stock is 10 or below.
Location: Physical place where item is kept. Example: Store Room, Rack 2, Almirah A.

Files changed
-------------
app/Support/StoreAccessScope.php
app/Support/AccessScope.php
app/Http/Controllers/StoreItemController.php
app/Http/Controllers/AssetController.php
app/Http/Controllers/StoreIndentController.php
resources/views/store_items/form.blade.php
resources/views/store_items/index.blade.php
resources/views/store_items/show.blade.php
resources/views/store-items/form.blade.php
resources/views/assets/form.blade.php
resources/views/assets/index.blade.php
resources/views/assets/show.blade.php
resources/views/store_indents/create.blade.php
resources/views/store_indents/index.blade.php
resources/views/store_indents/show.blade.php

After copying files
-------------------
Run:

composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve

Important data check
--------------------
For every Storekeeper user, users.college_id and users.department_id must be set correctly.
If those values are empty, the Add Store Item / Add Asset form cannot auto-populate the scope.
