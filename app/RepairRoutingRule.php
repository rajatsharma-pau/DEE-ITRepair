<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class RepairRoutingRule extends Model
{
    protected $fillable = ['repair_category_id','handler_type','handler_value','handler_employee_id','requires_store_verification','requires_store_incharge_approval','requires_programmer_verification','is_active'];
    protected $casts = ['requires_store_verification'=>'boolean','requires_store_incharge_approval'=>'boolean','requires_programmer_verification'=>'boolean','is_active'=>'boolean'];
    public function category(){ return $this->belongsTo(RepairCategory::class, 'repair_category_id'); }
    public function handlerEmployee(){ return $this->belongsTo(Employee::class, 'handler_employee_id'); }
}
