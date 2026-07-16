DEE Employee & IT Repair Management System - Laravel 5.8
=======================================================

This package is for a fresh Laravel 5.8 project.
It includes:

1. Phone number login, no self-registration
2. Employee master with service details, promotion/service movement and additional charges
3. Repair request workflow:
   Employee -> Storekeeper -> Vendor Estimate -> Programmer Technical Verification -> Storekeeper Proforma Print -> Manual D-4 process
4. Vendor master and estimate tracking
5. Financial sanction proforma print in DEE format
6. D-4 seat role for marking manually received file
7. Asset management:
   Computer, printer, scanner, UPS, chair, table, sound system, speaker, webcam, projector, furniture, electrical, other
   Asset states: In Store, With Employee, Under Repair, Returned to Store, Sent for Auction, Scrap/Auctioned, Lost
   Complete asset movement/history tracking
8. Store inventory management:
   Paper, pens, files, stationery and other consumable stock items
   Stock in, return, adjustment and issue movement history
9. Store indent workflow:
   Employee asks for stationery/material -> Storekeeper issues -> Stock automatically decreases
10. PIN code lookup endpoint for address autofill

Fresh setup steps
-----------------

1. Create Laravel 5.8 project:

composer create-project --prefer-dist laravel/laravel dee-it-repair "5.8.*"

2. Copy all folders from this ZIP into your project root.

3. Delete old duplicate migration files if you copied older versions earlier.
   This package already contains a complete clean migration set.

4. Configure .env database:

DB_DATABASE=dee_it_repair
DB_USERNAME=root
DB_PASSWORD=

5. Run:

composer dump-autoload
php artisan migrate:fresh
php artisan db:seed --class=DeeItRepairSeeder
php artisan storage:link
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan serve

Default logins
--------------

Admin       : 9876543210 / password
Storekeeper : 9876543211 / password
Programmer  : 9876543212 / password
Employee    : 9876543213 / password
Director    : 9876543214 / password
D-4 Seat    : 9876543216 / password

Important screens
-----------------

Employees       : /employees
Repair Requests : /repair-requests
Assets          : /assets
Store Stock     : /store-items
Store Indents   : /store-indents
Vendors         : /masters/vendors

Notes
-----

- Every repair/material request first goes to Storekeeper.
- Storekeeper enters vendor estimate and sends to Programmer for technical verification.
- Programmer only verifies technically and gives remarks; financial approval remains manual.
- Storekeeper prints financial sanction proforma and submits the physical file to D-4 seat.
- Asset management is separate from consumable inventory.
- Store indents are for consumable items like paper, pen, files etc. Stock is automatically reduced when Storekeeper issues items.

Latest update included in this package:
- Employees can view only assets allocated to them.
- Admin / Director / Storekeeper can view all assets and filter assets by individual employee.
- New repair request form now shows only the employee's allocated assets.
- Selecting an asset auto-fills category, item type, item name, inventory number and room/location.
- If asset is not listed, employee can submit a general request with minimum details.
- Default Problem / Material Requirement master added to reduce typing for employees.
- Problem templates are available category-wise on the request form.


UPDATE NOTE - Storekeeper Financial Sanction Screen Simplified
-------------------------------------------------------------
Storekeeper still enters Vendor, Estimated Amount and uploads Estimate/Quotation for a request.
After Programmer / Store Incharge marks Estimate OK, the Financial Sanction Proforma is generated automatically.
The following manual fields have been removed from the Storekeeper action form:
- Financial Sanction Proforma Fields
- Approval / Sanction Remarks
- Upload Signed Sanction

Manual D-4 submission and Sanction Received / Rejected status are still available as simple actions.
