<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class StoreIndent extends Model
{
    protected $fillable = ['indent_no','employee_id','college_id','department_id','issued_by_employee_id','status','required_date','issued_date','employee_remarks','storekeeper_remarks'];
    protected $dates = ['required_date','issued_date'];
    public function employee(){ return $this->belongsTo(Employee::class); }
    public function issuedBy(){ return $this->belongsTo(Employee::class, 'issued_by_employee_id'); }
    public function items(){ return $this->hasMany(StoreIndentItem::class); }
    public function stockMovements(){ return $this->hasMany(StoreStockMovement::class); }
    public function college(){ return $this->belongsTo(College::class); }
    public function department(){ return $this->belongsTo(Department::class); }

}
