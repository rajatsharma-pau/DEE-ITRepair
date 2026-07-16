<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class Asset extends Model
{
    protected $fillable = [
        'directorate_id','college_id','department_id','assigned_to_employee_id','asset_code','inventory_no','asset_category','item_name','make','model','serial_no','configuration','location',
        'purchase_date','purchase_amount','purchase_order_no','warranty_till','condition_status','asset_state','state_date','remarks'
    ];

    protected $dates = ['purchase_date','warranty_till','state_date'];

    public function directorate(){ return $this->belongsTo(Directorate::class); }
    public function assignedTo(){ return $this->belongsTo(Employee::class, 'assigned_to_employee_id'); }
    public function histories(){ return $this->hasMany(AssetHistory::class)->latest(); }
    public function repairRequests(){ return $this->hasMany(RepairRequest::class); }

    public function getDisplayNameAttribute()
    {
        return trim($this->item_name.' '.($this->make ? '('.$this->make.')' : ''));
    }
    public function college(){ return $this->belongsTo(College::class); }
    public function department(){ return $this->belongsTo(Department::class); }

}
