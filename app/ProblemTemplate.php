<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class ProblemTemplate extends Model
{
    protected $fillable = ['repair_category_id','title','description','item_group','is_active'];
    protected $casts = ['is_active'=>'boolean'];
    public function category(){ return $this->belongsTo(RepairCategory::class, 'repair_category_id'); }
}
