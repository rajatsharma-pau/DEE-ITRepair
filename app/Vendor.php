<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Vendor extends Model
{
    protected $fillable = [
        'name', 'contact_person', 'mobile', 'email', 'address',
        'gst_no', 'pan_no', 'vendor_type', 'is_active'
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function estimates()
    {
        return $this->hasMany(RepairEstimate::class);
    }
}
