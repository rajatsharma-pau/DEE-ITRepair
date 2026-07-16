<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DeeRepairRequest extends Model
{
    protected $table = 'dee_repair_requests';
    protected $fillable = ['request_no','employee_id','asset_id','item_type','item_name','inventory_no','problem_description','room_no','priority','status','storekeeper_id','programmer_id','storekeeper_remarks','programmer_remarks','employee_feedback','attachment','closed_at'];
    public function employee(){ return $this->belongsTo(DeeEmployee::class, 'employee_id'); }
    public function storekeeper(){ return $this->belongsTo(DeeEmployee::class, 'storekeeper_id'); }
    public function programmer(){ return $this->belongsTo(DeeEmployee::class, 'programmer_id'); }
    public function asset(){ return $this->belongsTo(DeeAsset::class, 'asset_id'); }
    public function logs(){ return $this->hasMany(DeeRepairLog::class, 'repair_request_id')->latest(); }
}
