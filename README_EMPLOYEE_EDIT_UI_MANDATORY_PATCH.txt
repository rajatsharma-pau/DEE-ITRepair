DEE Employee Add/Edit UI Mandatory Patch
=======================================

Replace:
- resources/views/employees/form.blade.php
- resources/views/employees/edit.blade.php
- resources/views/employees/create.blade.php

What changed:
- Cleaner Add/Edit Employee UI.
- Red mandatory * marks.
- Required HTML fields for important employee fields.
- College / Directorate and Department / Office / KVK auto-fill from logged-in user's scope.
- Department Admin scope locked to own department.
- College Admin/Admin/Director college locked, department selectable within own college.
- Superuser can choose any college/department.
- Removed Legacy Directorate dropdown from employee form.
- Better role multi-select display and help text.
- Better field help text for designation vs roles.
- Safe date formatting for edit forms.
- Better create/edit action bars.

After copying files run:

cd "E:\PAU Mobile Project\dee-it-repair"
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve

Optional:
- Apply snippets/controllers/EmployeeController_validation_note.txt to align backend validation with UI mandatory fields.
