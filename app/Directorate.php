<?php
namespace App;
use Illuminate\Database\Eloquent\Model;
class Directorate extends Model
{
    protected $fillable = ['name','short_name','is_active'];
    public function sections(){ return $this->hasMany(Section::class); }
    public function employees(){ return $this->hasMany(Employee::class); }
}
