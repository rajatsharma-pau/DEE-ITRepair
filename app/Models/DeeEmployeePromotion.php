<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DeeEmployeePromotion extends Model
{
    protected $table = 'dee_employee_promotions';
    protected $fillable = ['employee_id','old_designation','new_designation','promotion_date','remarks'];
    public function employee(){ return $this->belongsTo(DeeEmployee::class, 'employee_id'); }
}
