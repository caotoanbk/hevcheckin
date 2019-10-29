<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = ['EmployeeName', 'user_id', 'EmployeePhoto', 'EmployeeType', 'EmployeeCardname', 'EmployeeIdentity'];

    public function card(){
        return $this->hasOne(\App\Card::class, 'EmployeeIdentity', 'EmployeeIdentity');
    }

    public function supplier(){
        return $this->hasOne(\App\Supplier::class, 'id', 'supplier_id');
    }
}
