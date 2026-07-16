<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class AssetHistory extends Model
{
    protected $fillable = ['asset_id','employee_id','action_by','action_type','from_state','to_state','action_date','remarks'];
    protected $dates = ['action_date'];
    public function asset(){ return $this->belongsTo(Asset::class); }
    public function employee(){ return $this->belongsTo(Employee::class); }
    public function actionBy(){ return $this->belongsTo(User::class, 'action_by'); }
}
