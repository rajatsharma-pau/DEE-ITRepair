<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Designation extends Model
{
    protected $fillable = ['name','cadre','sort_order','is_active'];
    public function employees(){ return $this->hasMany(Employee::class); }
}
