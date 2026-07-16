<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $fillable = ['college_id','name','place','is_active'];
    public function college(){ return $this->belongsTo(College::class); }
    public function employees(){ return $this->hasMany(Employee::class); }
}
