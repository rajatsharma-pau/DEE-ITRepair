DEE Store Item Auto-Scope + Required Fields Patch
================================================

Purpose
-------
This patch fixes the Add Store Item page so that:

1. Storekeeper automatically gets his/her own College / Directorate.
2. Storekeeper automatically gets his/her own Department / Office / KVK.
3. Storekeeper cannot select another department.
4. Department Admin can view store records only and cannot add/edit stock.
5. Required fields show a red * mark.
6. Store item fields include help text for Opening Stock and Reorder Level.

Files
-----
1. app/Support/StoreAccessScope.php
   Replace or merge this file.

2. resources/views/store-items/form.blade.php
   Replace your store item form partial with this file.
   If your folder is named store_items instead of store-items, copy it there.

3. snippets/controllers/StoreItemController_changes.txt
   Apply these changes manually in your existing StoreItemController.

4. snippets/views/create_view_example.blade.php
   Only an example if your create view is missing the form wrapper.

Important
---------
For auto-population, the logged-in Storekeeper must have:

users.college_id
users.department_id

If these are blank, this patch also tries to read from the linked employee record:

users.employee.college_id
users.employee.department_id

But best practice is to update both users and employees when roles are assigned.

Commands
--------
After copying files, run:

composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

Field meaning
-------------
Brand:
Optional brand/company name, e.g. HP, Dell, Camlin, Kangaro.

Opening Stock:
Quantity available in store at the time of creating the item.
Example: 50 reams of A4 paper.

Reorder Level:
Minimum quantity after which system should show low stock warning.
Example: if reorder level is 10, then when stock becomes 10 or less, it appears in Low Stock.

Location:
Physical place where item is kept, e.g. Main Store Room, Rack 2, Almirah A.
