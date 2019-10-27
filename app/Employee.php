<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = ['EmployeeName', 'EmployeeInfo', 'EmployeePhoto', 'EmployeeType', 'EmployeeCardname', 'EmployeeIdentity'];

    public function card(){
        return $this->hasOne(\App\Card::class, 'EmployeeIdentity', 'EmployeeIdentity');
    }
}
