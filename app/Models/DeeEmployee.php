<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class DeeEmployee extends Authenticatable
{
    use Notifiable;
    protected $table = 'dee_employees';
    protected $fillable = ['name','phone','email','password','dob','designation','section','doj','last_promotion_date','retirement_date','room_no','role','status','must_change_password'];
    protected $hidden = ['password','remember_token'];
    protected $dates = ['dob','doj','last_promotion_date','retirement_date'];

    public function promotions(){ return $this->hasMany(DeeEmployeePromotion::class, 'employee_id'); }
    public function repairRequests(){ return $this->hasMany(DeeRepairRequest::class, 'employee_id'); }
    public function assets(){ return $this->hasMany(DeeAsset::class, 'assigned_to_employee_id'); }
    public function isRole($role){ return $this->role === $role; }
}
