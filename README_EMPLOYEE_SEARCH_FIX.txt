Employee Search Fix Patch
=========================

Problem:
Search was not working because the view/controller search parameter and columns were inconsistent.
Also, phone-login setup may not have users.login_id, so searching that column can break.

How to apply:
1. Open app/Http/Controllers/EmployeeController.php
2. Add this import at the top:
   use Illuminate\Support\Facades\Schema;

3. Replace only the index() method with the code in:
   snippets/EmployeeController_index_method.php

4. Open resources/views/employees/index.blade.php
5. Replace the existing search/filter form with:
   snippets/employees_index_search_form.blade.php

6. Run:
   composer dump-autoload
   php artisan view:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan serve

This search supports:
- Name
- Phone number
- Employee code
- Personal file/PF number
- GPF/NPS
- PAN
- User name/email/phone
- Designation
- College
- Department

It also keeps existing filters:
- College / Directorate
- Department / Office / KVK
- Status
