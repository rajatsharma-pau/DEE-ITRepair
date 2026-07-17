DEE Repair Request Show UI Final Patch
=====================================

Page fixed:
http://localhost:8000/repair-requests/{id}
Example: http://localhost:8000/repair-requests/1

Main file to replace:
resources/views/repair_requests/show.blade.php

What changed:
1. Cleaner professional UI for repair request detail page.
2. Header summary cards for handler, assigned employee, selected vendor and estimate amount.
3. Better status/priority badges.
4. Mandatory red * markers added for required fields.
5. Storekeeper form now clearly explains mandatory vendor estimate requirements.
6. Client-side dynamic required fields:
   - Vendor, Estimate Amount and PDF become mandatory when a new estimate is needed.
   - Programmer field appears only for forwarding to Programmer.
   - Store Incharge field appears only for forwarding to Store Incharge.
7. Financial sanction summary made sticky on desktop for faster work.
8. History/track section made scrollable so long logs do not make the page heavy.
9. Action cards are cleaner and collapsible.
10. Department Admin can view details, but action forms are not shown unless the user also has the correct functional role.

Recommended optional controller performance snippet:
snippets/controllers/RepairRequestController_show_method_performance_snippet.txt

Recommended optional backend permission hardening:
snippets/controllers/RepairRequestController_action_permission_hardening_snippet.txt

After copying files, run:

cd "E:\PAU Mobile Project\dee-it-repair"
composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve

Important:
This patch is mostly UI-safe. For full security, apply the action permission hardening snippet too because hiding a button in Blade is not enough.
