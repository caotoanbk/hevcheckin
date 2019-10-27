<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $fillable = ['CardName', 'EmployeeIdentity'];

    public function employee(){
        return $this->hasOne(\App\Employee::class, 'EmployeeCardname', 'CardName');
    }
}
