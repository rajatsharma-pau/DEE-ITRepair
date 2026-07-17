OTP column length fix
=====================

Reason for error:
The system is storing the OTP using Laravel Hash::make(). A hashed OTP is around 60 characters long, but the database column password_reset_otp was too small, so MySQL raised:

SQLSTATE[22001]: String data, right truncated: 1406 Data too long for column 'password_reset_otp'

Files included:
- database/migrations/2026_07_17_000001_fix_password_reset_otp_column_length.php

How to apply:
1. Copy the migration file into your Laravel project database/migrations folder.
2. Run:
   composer dump-autoload
   php artisan migrate
   php artisan config:clear
   php artisan cache:clear
   php artisan route:clear
   php artisan view:clear

No migrate:fresh is required.

Correct logic:
- Generate 6 digit OTP in controller.
- Store Hash::make($otp) in users.password_reset_otp.
- Send plain OTP by SMS.
- Verify user-entered OTP with Hash::check($request->otp, $user->password_reset_otp).
- After successful password reset, clear password_reset_otp and password_reset_otp_expires_at.
