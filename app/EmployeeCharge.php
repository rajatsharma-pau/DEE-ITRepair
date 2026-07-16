<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class EmployeeCharge extends Model
{
    protected $fillable = ['employee_id','charge_name','charge_type','from_date','to_date','is_active','remarks'];
    protected $dates = ['from_date','to_date'];
    protected $casts = ['is_active'=>'boolean'];
    public function employee(){ return $this->belongsTo(Employee::class); }
}
