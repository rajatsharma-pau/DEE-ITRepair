OTP SMS Patch for DEE IT Repair
================================

Files included:
1. app/Services/OtpSmsService.php
2. config/sms.php

Copy both into your Laravel project.

Add these values in .env:

SMSROOT_API_KEY=your_actual_key
SMSROOT_SENDER_ID=OSTPLS
SMSROOT_TEMPLATE_ID=your_actual_template_id
SMSROOT_ROUTE_ID=13
SMSROOT_CAMPAIGN=0

Important:
- sendOtp($phone, $otp) only sends the OTP.
- Do not generate OTP again inside sendOtp().
- Do not update user password inside sendOtp().
- Controller should generate OTP, save it with expiry, call sendOtp(), then reset password only after OTP verification.

After copying, run:

composer dump-autoload
php artisan config:clear
php artisan cache:clear
php artisan route:clear
