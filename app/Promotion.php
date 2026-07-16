<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Promotion extends Model
{
    protected $guarded = [];

    protected $dates = ['promotion_date'];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }
}
