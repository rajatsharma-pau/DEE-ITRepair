<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class EmployeeServiceMovement extends Model
{
    protected $fillable = ['employee_id','movement_type','from_designation_id','to_designation_id','manual_from_designation','manual_to_designation','effective_date','order_no','order_date','document_path','remarks','created_by'];
    protected $dates = ['effective_date','order_date'];
    public function employee(){ return $this->belongsTo(Employee::class); }
    public function fromDesignation(){ return $this->belongsTo(Designation::class, 'from_designation_id'); }
    public function toDesignation(){ return $this->belongsTo(Designation::class, 'to_designation_id'); }
    public function creator(){ return $this->belongsTo(User::class, 'created_by'); }
}
