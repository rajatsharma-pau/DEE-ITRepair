# Repair Request Employee Auto-fill Patch

## Issue fixed

On `/repair-requests/create`, the save action was showing:

`Employee is required.`

This happened because the form did not always send `employee_id`, especially for multi-role users such as:

- Department Admin + Employee
- Storekeeper + Employee
- Programmer + Employee

## File included

Copy this file into your Laravel project:

`app/Http/Controllers/RepairRequestController.php`

## What changed

In `RepairRequestController@store`, before validation:

- Employee-only users are forced to their own employee record.
- Multi-role users with a linked employee record are auto-filled to their own employee record when `employee_id` is blank.
- Admin users can still select another employee when `employee_id` is submitted from the form.

## After copying

Run:

```bash
cd "E:\PAU Mobile Project\dee-it-repair"

composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve
```

## Important check

Make sure the logged-in user has a linked employee record:

`users.id = employees.user_id`
