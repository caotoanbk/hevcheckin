<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\User;

class Employee extends Model
{

    protected $primaryKey = 'EmployeeCode';
    public $incrementing = false;

    protected $fillable = ['EmployeeName', 'SupplierName', 'EmployeePhoto', 'EmployeeType', 'EmployeeCardname', 'EmployeeCode'];

    public function card(){
        return $this->hasOne(\App\Card::class, 'CardName', 'EmployeeCardname');
    }
}
