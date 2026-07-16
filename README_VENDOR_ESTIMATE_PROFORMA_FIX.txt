DEE IT Repair - Vendor Estimate + Department Verification + Correct Proforma Patch

What this patch changes:
1. Storekeeper receives the repair request first.
2. Storekeeper must select vendor, enter estimated amount, and upload estimate / quotation PDF for a new estimate.
3. Storekeeper can forward the request to either Programmer or Store Incharge. This supports department-wise variation.
4. Programmer / Store Incharge verifies physically and returns remarks to Storekeeper.
5. Only after Estimate OK, Storekeeper can generate and print Financial Sanction Proforma.
6. Financial Sanction Proforma wording has been corrected. It no longer creates the awkward sentence where item details appear before 'may kindly be accorded'.

Files included:
- app/Http/Controllers/RepairRequestController.php
- resources/views/repair_requests/show.blade.php
- resources/views/repair_requests/proforma.blade.php

How to apply:
1. Copy these folders/files into your Laravel project root.
2. Replace existing files when asked.
3. Run:
   composer dump-autoload
   php artisan view:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan serve

No database migration is required for this patch.
