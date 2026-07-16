<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class EmployeeTransfer extends Model
{
    protected $fillable = [
        'employee_id','from_college_id','from_department_id','to_college_id','to_department_id',
        'transfer_date','relieving_date','joining_date','order_no','order_date','order_file','remarks','created_by'
    ];
    protected $dates = ['transfer_date','relieving_date','joining_date','order_date'];
    public function employee(){ return $this->belongsTo(Employee::class); }
    public function fromCollege(){ return $this->belongsTo(College::class, 'from_college_id'); }
    public function fromDepartment(){ return $this->belongsTo(Department::class, 'from_department_id'); }
    public function toCollege(){ return $this->belongsTo(College::class, 'to_college_id'); }
    public function toDepartment(){ return $this->belongsTo(Department::class, 'to_department_id'); }
    public function creator(){ return $this->belongsTo(User::class, 'created_by'); }
}
