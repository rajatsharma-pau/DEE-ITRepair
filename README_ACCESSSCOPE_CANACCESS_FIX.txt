Patch: AccessScope missing canAccessEmployee()

Issue:
Symfony Debug FatalThrowableError:
Call to undefined method App\Support\AccessScope::canAccessEmployee()

Fix:
Replace app/Support/AccessScope.php with the file included in this patch.

Then run:
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve

This AccessScope implements:
- canAccessEmployee()
- canEditEmployee()
- canTransferEmployee()
- canAssignRolesToEmployee()
- allowedAssignableRoles()
- colleges()
- departments()
- transferDestinationColleges()
- transferDestinationDepartments()
- apply()

Final scope rule implemented:
Superuser:
- Can assign any role
- Can transfer anywhere

College Admin / Admin / Director:
- Can manage employees of own college/directorate
- Can assign limited roles to employees of own college/directorate
- Can transfer those employees to any college/directorate/department

Department Admin:
- Can see/edit only own department employees
- Can assign limited roles only to own department employees
- Can transfer own department employees to any department under any college/directorate
