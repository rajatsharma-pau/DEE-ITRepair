<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class RepairLog extends Model
{
    protected $fillable = ['repair_request_id','action_by','action','old_status','new_status','remarks'];
    public function request(){ return $this->belongsTo(RepairRequest::class, 'repair_request_id'); }
    public function user(){ return $this->belongsTo(User::class, 'action_by'); }
}
