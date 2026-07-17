// Add at the top of EmployeeController.php:
use App\Support\AccessScope;

// In edit/create methods, pass assignable roles to view:
$assignableRoles = AccessScope::assignableRoles($employee ?? null);

// In update() method after saving employee and user, replace old role sync code with:
$roleSlugs = $request->input('roles', ['employee']);
AccessScope::syncEmployeeRoles($employee, $roleSlugs);

// In store() method after creating employee and user, use:
$roleSlugs = $request->input('roles', ['employee']);
AccessScope::syncEmployeeRoles($employee, $roleSlugs);
