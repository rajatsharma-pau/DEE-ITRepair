ROLE SLUG FIX PATCH

Problem:
The seeder Dee46EmployeesPhoneLoginSeeder searches the roles table using the slug column:
    where slug = employee
But your current roles table does not have a slug column.

Fix:
This patch adds the slug column to roles and fills required roles:
- superuser
- admin
- college_admin
- department_admin
- employee
- storekeeper
- programmer
- director
- d4_seat

Steps:
1. Copy database/migrations/2026_06_16_000004_add_slug_to_roles_table.php into your Laravel project.
2. Run:
   composer dump-autoload
   php artisan migrate
   php artisan db:seed --class=Dee46EmployeesPhoneLoginSeeder
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
