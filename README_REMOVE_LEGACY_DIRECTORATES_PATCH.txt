Purpose
-------
This patch removes the separate legacy `directorates` table concept.

Final structure:
- `colleges` table = Colleges and Directorates
  Example: Directorate of Extension Education
- `departments` table = Departments / Offices / KVKs under a college/directorate
  Example: Directorate of Extension Education, Communication Centre, KVK Amritsar
- `sections` table = internal sections and now links to college_id / department_id

What changed
------------
1. Removed directorate_id from Employee, Asset, StoreItem, and Section models/forms/controllers.
2. Removed the Legacy Directorate dropdown from employees/form.blade.php.
3. Replaced create_directorates_table migration with a no-op migration.
4. Updated fresh migrations so employees, assets, store_items, sections do not need directorate_id.
5. Added cleanup migration: 2026_07_17_000030_remove_legacy_directorates_table.php
   This drops old directorate_id columns and the old directorates table from an existing database.
6. Updated DeeItRepairSeeder and EmployeeDetailsSeeder so they use College + Department only.

How to apply
------------
1. Backup project and database.
2. Copy patch files over your Laravel project.
3. Run:
   composer dump-autoload
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear
   php artisan migrate

If you are doing a fresh rebuild, you can use:
   php artisan migrate:fresh --seed

Compatibility note
------------------
The patch includes app/Directorate.php as a backward-compatibility alias to the colleges table, so any old accidental reference will not use a separate directorates table. Do not delete it unless you are sure no old code references App\Directorate.

Important
---------
Keep the label "College / Directorate" in the UI. That is only a label. It does not mean a separate directorates table.
