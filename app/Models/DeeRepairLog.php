<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DeeRepairLog extends Model
{
    protected $table = 'dee_repair_logs';
    protected $fillable = ['repair_request_id','action_by','action','old_status','new_status','remarks'];
    public function request(){ return $this->belongsTo(DeeRepairRequest::class, 'repair_request_id'); }
    public function actor(){ return $this->belongsTo(DeeEmployee::class, 'action_by'); }
}
