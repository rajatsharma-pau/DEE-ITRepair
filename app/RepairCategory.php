<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class RepairCategory extends Model
{
    protected $fillable = ['name','item_group','default_handler','is_active'];
    protected $casts = ['is_active'=>'boolean'];
    public function routingRules(){ return $this->hasMany(RepairRoutingRule::class); }
    public function problemTemplates(){ return $this->hasMany(ProblemTemplate::class); }
}
