<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class StoreIndentItem extends Model
{
    protected $fillable = ['store_indent_id','store_item_id','requested_qty','approved_qty','issued_qty','remarks'];
    public function indent(){ return $this->belongsTo(StoreIndent::class, 'store_indent_id'); }
    public function storeItem(){ return $this->belongsTo(StoreItem::class); }
}
