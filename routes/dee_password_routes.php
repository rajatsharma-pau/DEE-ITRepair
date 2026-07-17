<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| DEE Password / OTP Routes
|--------------------------------------------------------------------------
| Add this line at the bottom of routes/web.php:
| require __DIR__.'/dee_password_routes.php';
*/

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', 'Auth\\ForgotPasswordOtpController@showForgotForm')->name('password.otp.request');
    Route::post('/forgot-password/send-otp', 'Auth\\ForgotPasswordOtpController@sendOtp')->name('password.otp.send');
    Route::get('/reset-password-otp', 'Auth\\ForgotPasswordOtpController@showResetForm')->name('password.otp.reset.form');
    Route::post('/reset-password-otp', 'Auth\\ForgotPasswordOtpController@resetPassword')->name('password.otp.reset');
});

Route::middleware('auth')->group(function () {
    Route::get('/my-profile/change-password', 'ProfilePasswordController@showChangePasswordForm')->name('profile.password.change');
    Route::post('/my-profile/change-password', 'ProfilePasswordController@changePassword')->name('profile.password.update');
});
