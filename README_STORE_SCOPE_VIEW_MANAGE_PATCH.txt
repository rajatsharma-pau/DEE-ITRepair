DEE Store / Stock / Asset / Indent Permission Patch
====================================================

Purpose
-------
This patch implements the final rule discussed:

1. Superuser
   - Full access to stock/assets/indents across PAU.
   - Can add/edit/delete/issue/reject/allocate anywhere.

2. Storekeeper
   - Can add/edit/manage stock only for own department.
   - Can add/edit/manage assets only for own department.
   - Can process/issue/reject indents only for own department.
   - Backend forces college_id and department_id to the Storekeeper's assigned scope.

3. Department Admin
   - Can view stock/assets/indents of own department only.
   - Cannot add stock.
   - Cannot edit stock.
   - Cannot add/edit assets.
   - Cannot issue/reject indents.

4. College Admin / Admin / Director
   - Can view stock/assets/indents as per own college/directorate scope.
   - Cannot perform store operations unless also assigned Storekeeper role.

5. Employee
   - Can see own allocated assets and own indents only.

Files Included
--------------
1. app/Support/StoreAccessScope.php
   Drop-in permission/scope class for Store module.

2. app/Http/Middleware/StoreViewAllowed.php
   Middleware for store/stock/assets/indents view pages.

3. app/Http/Middleware/StoreManageOnly.php
   Middleware for add/edit/delete/issue/reject/allocate actions.

4. snippets/controllers/StoreItemController_changes.php
   Example changes for stock/store item controller.

5. snippets/controllers/AssetController_changes.php
   Example changes for asset controller.

6. snippets/controllers/IndentController_changes.php
   Example changes for indent controller.

7. snippets/views/*.blade.php
   Blade snippets to hide Add/Edit/Issue/Reject buttons for view-only roles.

8. snippets/routes/store_routes_permission_example.php
   Route and middleware example.

How to Apply
------------
Step 1: Copy these files into your Laravel project:

app/Support/StoreAccessScope.php
app/Http/Middleware/StoreViewAllowed.php
app/Http/Middleware/StoreManageOnly.php

Step 2: Register middleware in app/Http/Kernel.php:

'routeMiddleware' => [
    // existing middleware...
    'store.view' => \App\Http\Middleware\StoreViewAllowed::class,
    'store.manage' => \App\Http\Middleware\StoreManageOnly::class,
]

Step 3: Update your Store/Stock, Asset, and Indent controllers using the snippets.

Important controller rules:

- All index/list pages should use scoped query:
  StoreAccessScope::applyStockScope($query)
  StoreAccessScope::applyAssetScope($query)
  StoreAccessScope::applyIndentScope($query)

- All store/create/update/delete/issue/reject/allocate actions should call:
  StoreAccessScope::assertCanManageStore()
  or
  StoreAccessScope::assertCanManageRecord($record)

- On store and asset create/update, call:
  $data = StoreAccessScope::forceStorekeeperDepartment($data);

This prevents Storekeeper from adding stock/assets to any other department by changing hidden fields.

Step 4: Update routes using the example in snippets/routes/store_routes_permission_example.php.

Step 5: Update Blade views so Add/Edit/Issue/Reject buttons show only when:

\App\Support\StoreAccessScope::canManageRecord($record)

Commands After Applying
-----------------------
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve

Testing Checklist
-----------------
1. Login as Department Admin of Communication Centre:
   - Stock list shows only Communication Centre stock.
   - Add/Edit/Delete/Stock In/Stock Out buttons are hidden.
   - Direct URL to create/edit should give 403.
   - Indents are visible but Issue/Reject buttons hidden.

2. Login as Storekeeper of Communication Centre:
   - Can add stock only for Communication Centre.
   - Can add assets only for Communication Centre.
   - Can issue/reject Communication Centre indents.
   - Cannot edit KVK Amritsar stock/assets by URL.

3. Login as College Admin DEE:
   - Can view DEE/directorate store records.
   - Cannot perform store actions unless also Storekeeper.

4. Login as Superuser:
   - Full access everywhere.

Note
----
If your model/table column names differ from these names, adjust the column arguments:

StoreAccessScope::applyStockScope($query, 'department_id', 'college_id')
StoreAccessScope::applyAssetScope($query, 'department_id', 'college_id', 'employee_id')
StoreAccessScope::applyIndentScope($query, 'department_id', 'college_id', 'employee_id', 'user_id')
