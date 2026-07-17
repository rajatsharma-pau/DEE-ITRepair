DEE AccessScope collegeId() / departmentId() Fix
=================================================

Error fixed:
Call to undefined method App\Support\AccessScope::collegeId()

Why it happened:
Your employee add form/controller is calling AccessScope::collegeId() for Department Admin defaults, but the current AccessScope.php does not have that method.

Recommended fix:
1. Replace this file in your project:
   app/Support/AccessScope.php

2. Run:
   composer dump-autoload
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear

What is included:
- collegeId()
- departmentId()
- college()
- department()
- apply()
- canAccessEmployee()
- canEditEmployee()
- canTransferEmployee()
- canAssignRolesToEmployee()
- allowedAssignableRoles()
- roleOptions()
- colleges()
- departments()
- transferDestinationColleges()
- transferDestinationDepartments()
- applyDefaultScopeToData()
- scopeLabel()
- isEmployeeOnly()

Final rule kept:
Superuser: full access.
College Admin / Admin / Director: manage employees of own college/directorate, transfer those employees anywhere.
Department Admin: manage employees of own department, transfer those employees anywhere.
Employee: own record only.

Important:
For add employee by Department Admin, use users.college_id and users.department_id as default scope. If users table has blank college_id/department_id for that Department Admin, update it in database or from employee edit screen.
