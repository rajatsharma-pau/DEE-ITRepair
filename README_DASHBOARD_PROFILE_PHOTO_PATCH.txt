DEE IT Repair - Dashboard and Profile Photo Patch

This patch adds:
1. Employee dashboard with clickable small cards.
2. Role/admin dashboard with smaller clickable cards.
3. Profile photo shown in navbar.
4. My Profile page where employee can upload/remove profile photo.
5. /dashboard route alias for /home.
6. Repair request handler filter and store indent status filter for dashboard links.

Copy the folders into your Laravel 5.8 project and replace existing files.
Then run:
composer dump-autoload
php artisan view:clear
php artisan cache:clear
php artisan route:clear
php artisan storage:link
php artisan serve

No database migration is required if employees.photo already exists.
