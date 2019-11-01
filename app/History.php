<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Employee;

class History extends Model
{
    protected $fillable = ['CardName' ,'EmployeeName', 'SupplierName'];

}
