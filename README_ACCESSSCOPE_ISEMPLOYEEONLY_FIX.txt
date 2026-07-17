Patch: AccessScope missing isEmployeeOnly()

Problem:
Symfony\Component\Debug\Exception\FatalThrowableError
Call to undefined method App\Support\AccessScope::isEmployeeOnly()

Fix:
Replace app/Support/AccessScope.php with the file in this patch.

Commands after copying:
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve

Included methods:
- isEmployeeOnly()
- canAccessEmployee()
- canEditEmployee()
- canTransferEmployee()
- canAssignRolesToEmployee()
- allowedAssignableRoles()
- transferDestinationColleges()
- transferDestinationDepartments()
- apply()
- colleges()
- departments()

Final rule implemented:
- Superuser: can access/transfer/assign everywhere.
- College Admin/Admin/Director: can manage employees currently in own college/directorate and transfer them anywhere.
- Department Admin: can manage employees currently in own department and transfer them anywhere.
- Employee-only: sees own records only.
