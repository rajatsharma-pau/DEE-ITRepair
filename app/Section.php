<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Section extends Model
{
    protected $fillable = ['directorate_id','name','short_name','is_active'];
    public function directorate(){ return $this->belongsTo(Directorate::class); }
    public function employees(){ return $this->hasMany(Employee::class); }
}
