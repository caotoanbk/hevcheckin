<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use App\Employee;

class History extends Model
{
    protected $fillable = ['CardName', 'EmployeeIdentity' ,'EmployeeName', 'SupplierName'];

    protected $appends = ['employee_name'];

    public function getEmployeeNameAttribute(){
    	$employee = Employee::where('EmployeeIdentity', $this->EmployeeIdentity)->first();
    	if($employee){
    		return $employee->EmployeeName;
    	}
    	return '';
    }
}
