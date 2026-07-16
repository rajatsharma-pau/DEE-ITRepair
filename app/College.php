<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class College extends Model
{
    protected $fillable = ['name','short_name','is_active'];
    public function departments(){ return $this->hasMany(Department::class); }
    public function employees(){ return $this->hasMany(Employee::class); }
}
