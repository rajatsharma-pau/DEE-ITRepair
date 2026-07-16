<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PincodeController extends Controller
{
    public function lookup($pincode)
    {
        $pincode = preg_replace('/[^0-9]/', '', $pincode);
        if (strlen($pincode) != 6) {
            return response()->json(['success' => false, 'message' => 'Invalid PIN code.']);
        }

        $url = 'https://api.postalpincode.in/pincode/'.$pincode;
        $json = @file_get_contents($url);
        if (!$json) {
            return response()->json(['success' => false, 'message' => 'PIN API not reachable. Fill city/state manually.']);
        }
        $data = json_decode($json, true);
        if (!isset($data[0]['Status']) || $data[0]['Status'] != 'Success' || empty($data[0]['PostOffice'][0])) {
            return response()->json(['success' => false, 'message' => 'No record found.']);
        }
        $po = $data[0]['PostOffice'][0];
        return response()->json([
            'success' => true,
            'city' => $po['Block'] ?: $po['District'],
            'district' => $po['District'],
            'state' => $po['State'],
            'country' => $po['Country'],
            'post_office' => $po['Name'],
        ]);
    }
}
