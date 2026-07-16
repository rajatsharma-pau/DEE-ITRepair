<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class StoreItem extends Model
{
    protected $fillable = ['directorate_id','college_id','department_id','item_code','name','category','brand','unit','opening_stock','current_stock','reorder_level','location','description','is_active'];
    protected $casts = ['is_active' => 'boolean'];
    public function directorate(){ return $this->belongsTo(Directorate::class); }
    public function indentItems(){ return $this->hasMany(StoreIndentItem::class); }
    public function stockMovements(){ return $this->hasMany(StoreStockMovement::class)->latest(); }
    public function getStockLabelAttribute(){ return rtrim(rtrim(number_format($this->current_stock, 2, '.', ''), '0'), '.').' '.$this->unit; }
    public function college(){ return $this->belongsTo(College::class); }
    public function department(){ return $this->belongsTo(Department::class); }

}
