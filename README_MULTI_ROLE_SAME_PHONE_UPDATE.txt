DEE IT Repair - Multi Role / Same Phone Update

Purpose:
- One phone number remains unique in users table.
- Do NOT create duplicate users with the same phone.
- Same person can now have multiple roles on one login, e.g. Department Admin + Storekeeper, Admin + D-4 Seat, Assistant by designation + Admin role.

Important concept:
- Designation is service post: Clerk, Assistant, Senior Assistant, Professor, etc.
- Role is system permission: employee, storekeeper, programmer, department_admin, college_admin, superuser, etc.
- Additional charge is duty: Store Incharge, Head, Incharge, etc.

New tables:
- roles
- user_roles

Existing users.role remains as primary/default role for backward compatibility.
The actual access check now uses user_roles also.

After copying patch/full package:
composer dump-autoload
php artisan migrate:fresh
php artisan db:seed --class=DeeItRepairSeeder
php artisan view:clear
php artisan cache:clear
php artisan route:clear
php artisan serve

Seeder example:
Phone 9876543211 has Department Admin + Storekeeper roles.
