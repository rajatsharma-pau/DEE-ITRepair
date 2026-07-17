DEE Final Store + Layout + Scope Patch
======================================

Copy these files into your Laravel 5.8 project:

1) resources/views/layouts/app.blade.php
   - Final navbar/menu file.
   - Uses users.college_id and users.department_id as primary login scope.
   - Shows Store/Assets menus according to role.
   - Department Admin can view store records of own department but cannot see Add Stock/Add Asset management buttons.
   - Storekeeper can manage store/asset/indent records only as controlled by controller/middleware.
   - Superuser sees full menu.
   - Master menus are shown only to Superuser.

2) app/Support/StoreAccessScope.php
   - Central helper for store stock/assets/indents scope.
   - Superuser: full access.
   - Storekeeper: manage own department only.
   - Department Admin: view own department only.
   - College Admin/Admin/Director: view own college/directorate only.
   - Employee: own assets/own indents only.

3) app/Http/Middleware/StoreViewAllowed.php
4) app/Http/Middleware/StoreManageOnly.php
   - Middleware for store view/manage route separation.

5) snippets/
   - Manual controller, route, and button snippets.

Important Rules Implemented
---------------------------
Department Admin:
- View stock/assets/indents of own department.
- Cannot add/edit stock.
- Cannot add/edit assets.
- Cannot issue/reject indents.

Storekeeper:
- Add/edit/manage stock for own department.
- Add/edit/manage assets for own department.
- Process indents for own department.

College Admin / Admin / Director:
- Manage employees of own college/directorate.
- Store module is view-only as per college/directorate unless they also have Storekeeper role.

Superuser:
- Full access everywhere.

After Copying Files
-------------------
Run from project root:

composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

If route middleware is not registered, add this to app/Http/Kernel.php:

'store.view' => \App\Http\Middleware\StoreViewAllowed::class,
'store.manage' => \App\Http\Middleware\StoreManageOnly::class,

Testing
-------
1. Login as Department Admin:
   - Check Store Stock: visible only for same department.
   - Add Stock/Add Asset buttons should not show.
   - Direct create/update URLs should give 403 if store.manage is applied.

2. Login as Storekeeper:
   - Can add stock/assets only for own department.
   - Can process own department indents.

3. Login as Superuser:
   - Full access.
