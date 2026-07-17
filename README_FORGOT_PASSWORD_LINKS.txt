DEE Forgot Password OTP + Profile Change Password Patch
=======================================================

This patch adds:
1. Forgot Password link on login page.
2. OTP based password reset using phone number.
3. Change Password screen after login.
4. OTP SMS service using SMSRoot gateway config.

FILES INCLUDED
--------------
routes/dee_password_routes.php
app/Http/Controllers/Auth/ForgotPasswordOtpController.php
app/Http/Controllers/ProfilePasswordController.php
app/Services/OtpSmsService.php
config/sms.php
database/migrations/2026_07_16_000010_add_otp_password_fields_to_users_table.php
resources/views/auth/login.blade.php
resources/views/auth/forgot-phone.blade.php
resources/views/auth/reset-otp.blade.php
resources/views/profile/change-password.blade.php

INSTALLATION
------------
1. Copy all files into your Laravel project.

2. Add this line at the bottom of routes/web.php:

require __DIR__.'/dee_password_routes.php';

3. Add SMS details in .env:

SMSROOT_API_KEY=your_actual_key
SMSROOT_SENDER_ID=OSTPLS
SMSROOT_TEMPLATE_ID=your_actual_template_id
SMSROOT_ROUTE_ID=13
SMSROOT_CAMPAIGN=0

4. Run:

composer dump-autoload
php artisan migrate
php artisan config:clear
php artisan cache:clear
php artisan route:clear
php artisan view:clear
php artisan serve

WHERE LINKS WILL SHOW
---------------------
Login page:
Forgot Password? Reset using OTP

Profile/change password URL:
/my-profile/change-password

To show Change Password inside navbar user dropdown, add this link manually inside resources/views/layouts/app.blade.php user dropdown:

<a class="dropdown-item" href="{{ route('profile.password.change') }}">Change Password</a>

IMPORTANT
---------
Forgot Password is only for users who are not logged in.
Change Password is for logged in users.
