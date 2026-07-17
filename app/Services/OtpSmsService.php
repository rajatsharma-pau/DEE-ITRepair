<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class OtpSmsService
{
    public function sendOtp($phone, $otp)
    {
        $apiKey = config('sms.smsroot.api_key');
        $senderId = config('sms.smsroot.sender_id', 'OSTPLS');
        $templateId = config('sms.smsroot.template_id');
        $routeId = config('sms.smsroot.route_id', '13');
        $campaign = config('sms.smsroot.campaign', '0');

        $message = urlencode('Hello DEE IT Repair User, Please use this OTP: ' . $otp . ' to verify your phone number. ');

        $url = 'http://bulksms.smsroot.com/app/smsapi/index.php?key=' . urlencode($apiKey)
            . '&campaign=' . urlencode($campaign)
            . '&routeid=' . urlencode($routeId)
            . '&type=text'
            . '&contacts=' . urlencode($phone)
            . '&senderid=' . urlencode($senderId)
            . '&msg=' . $message
            . '&template_id=' . urlencode($templateId);
//dd( $url );


$ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => false,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT => 30,
        ]);

        $output = curl_exec($ch);
        $error = curl_error($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            Log::error('OTP SMS sending failed', [
                'phone' => $phone,
                'error' => $error,
            ]);
            return false;
        }

        Log::info('OTP SMS gateway response', [
            'phone' => $phone,
            'http_code' => $httpCode,
            'response' => $output,
        ]);

        return true;
    }
}
