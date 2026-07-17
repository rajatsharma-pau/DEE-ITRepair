DEE AccessScope canAccessDepartment/canAccessCollege Fix

Problem:
Call to undefined method App\Support\AccessScope::canAccessDepartment()

Reason:
EmployeeController has authorizeScopeFields() calling:
- AccessScope::canAccessDepartment($data['department_id'])
- AccessScope::canAccessCollege($data['college_id'])

Fix option A (recommended):
Replace app/Support/AccessScope.php with the file included in this patch.

Fix option B:
Open snippets/AccessScope_missing_methods_only.txt and paste the two methods inside your existing AccessScope class before the final closing brace.

After applying:
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear

Rule implemented:
- Superuser can access any college/department.
- Admin / College Admin / Director can add/edit employees only within own college/directorate.
- Department Admin can add/edit employees only within own department.
- Storekeeper scope is own college/department for store-related forms.
- Transfer destination anywhere remains handled by canTransferEmployee() and transferDestinationColleges()/transferDestinationDepartments().
