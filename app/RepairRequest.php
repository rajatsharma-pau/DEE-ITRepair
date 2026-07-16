<?php
namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class RepairRequest extends Model
{
    protected $fillable = [
        'request_no', 'employee_id', 'college_id', 'department_id', 'repair_category_id', 'asset_id', 'problem_template_id', 'selected_estimate_id',
        'assigned_to_employee_id', 'current_handler_role',
        'storekeeper_verified_by', 'programmer_verified_by', 'd4_received_by',
        'item_type', 'item_name', 'inventory_no', 'room_no', 'priority',
        'problem_description', 'attachment', 'status',
        'storekeeper_remarks', 'programmer_work_done', 'programmer_remarks',
        'programmer_estimate_status', 'd4_remarks', 'approval_remarks', 'employee_feedback',
        'requires_financial_sanction', 'financial_sanction_amount',
        'financial_sanction_purpose', 'purchase_payment_type', 'vehicle_no',
        'scheme_name', 'enclosure_details', 'proforma_date', 'proforma_generated_by',
        'proforma_generated_at', 'proforma_printed_at', 'd4_submitted_at', 'd4_received_at',
        'manual_sanction_status', 'signed_sanction_file',
        'storekeeper_received_at', 'storekeeper_verified_at', 'forwarded_to_programmer_at',
        'programmer_received_at', 'programmer_completed_at', 'closed_at'
    ];

    protected $dates = [
        'proforma_date', 'proforma_generated_at', 'proforma_printed_at',
        'd4_submitted_at', 'd4_received_at', 'storekeeper_received_at',
        'storekeeper_verified_at', 'forwarded_to_programmer_at',
        'programmer_received_at', 'programmer_completed_at', 'closed_at'
    ];

    protected $casts = ['requires_financial_sanction' => 'boolean'];

    public function employee(){ return $this->belongsTo(Employee::class); }
    public function category(){ return $this->belongsTo(RepairCategory::class, 'repair_category_id'); }
    public function asset(){ return $this->belongsTo(Asset::class); }
    public function problemTemplate(){ return $this->belongsTo(ProblemTemplate::class); }
    public function assignedTo(){ return $this->belongsTo(Employee::class, 'assigned_to_employee_id'); }
    public function storekeeper(){ return $this->belongsTo(Employee::class, 'storekeeper_verified_by'); }
    public function programmer(){ return $this->belongsTo(Employee::class, 'programmer_verified_by'); }
    public function d4Receiver(){ return $this->belongsTo(Employee::class, 'd4_received_by'); }
    public function proformaGeneratedBy(){ return $this->belongsTo(Employee::class, 'proforma_generated_by'); }
    public function estimates(){ return $this->hasMany(RepairEstimate::class); }
    public function selectedEstimate(){ return $this->belongsTo(RepairEstimate::class, 'selected_estimate_id'); }
    public function logs(){ return $this->hasMany(RepairLog::class)->orderBy('created_at','desc'); }
    public function getAttachmentUrlAttribute(){ return $this->attachment ? Storage::url($this->attachment) : null; }
    public function getSignedSanctionUrlAttribute(){ return $this->signed_sanction_file ? Storage::url($this->signed_sanction_file) : null; }

    public function getIsComputerRelatedAttribute()
    {
        return $this->category && $this->category->item_group == 'Computer Related';
    }

    public function getProformaAmountAttribute()
    {
        if ($this->financial_sanction_amount) return $this->financial_sanction_amount;
        return $this->selectedEstimate ? $this->selectedEstimate->estimate_amount : null;
    }

    public function getSelectedVendorNameAttribute()
    {
        return $this->selectedEstimate && $this->selectedEstimate->vendor ? $this->selectedEstimate->vendor->name : null;
    }
    public function college(){ return $this->belongsTo(College::class); }
    public function department(){ return $this->belongsTo(Department::class); }

}
