<?php
namespace App;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $fillable = ['college_id', 'department_id', 'name', 'short_name', 'is_active'];

    public function college(){ return $this->belongsTo(College::class); }
    public function department(){ return $this->belongsTo(Department::class); }
    public function employees(){ return $this->hasMany(Employee::class); }
}
