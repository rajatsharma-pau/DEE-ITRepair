<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RepairEstimate extends Model
{
    protected $fillable = [
        'repair_request_id', 'vendor_id', 'estimate_amount', 'estimate_date',
        'estimate_details', 'estimate_file', 'is_selected', 'entered_by',
        'programmer_verified_by', 'programmer_verification_status',
        'programmer_remarks', 'programmer_verified_at'
    ];

    protected $dates = ['estimate_date', 'programmer_verified_at'];
    protected $casts = ['is_selected' => 'boolean'];

    public function repairRequest()
    {
        return $this->belongsTo(RepairRequest::class);
    }

    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function enteredBy()
    {
        return $this->belongsTo(Employee::class, 'entered_by');
    }

    public function programmer()
    {
        return $this->belongsTo(Employee::class, 'programmer_verified_by');
    }

    public function getEstimateFileUrlAttribute()
    {
        return $this->estimate_file ? Storage::url($this->estimate_file) : null;
    }
}
