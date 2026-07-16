<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class DeeAsset extends Model
{
    protected $table = 'dee_assets';
    protected $fillable = ['inventory_no','item_type','item_name','make_model','assigned_to_employee_id','purchase_date','warranty_till','status'];
    public function employee(){ return $this->belongsTo(DeeEmployee::class, 'assigned_to_employee_id'); }
}
