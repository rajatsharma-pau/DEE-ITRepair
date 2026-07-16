<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class StoreStockMovement extends Model
{
    protected $fillable = ['store_item_id','store_indent_id','employee_id','created_by','movement_type','quantity','balance_after','movement_date','remarks'];
    protected $dates = ['movement_date'];
    public function storeItem(){ return $this->belongsTo(StoreItem::class); }
    public function indent(){ return $this->belongsTo(StoreIndent::class, 'store_indent_id'); }
    public function employee(){ return $this->belongsTo(Employee::class); }
    public function createdBy(){ return $this->belongsTo(User::class, 'created_by'); }
}
