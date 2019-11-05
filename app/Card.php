<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Card extends Model
{
    protected $primaryKey = 'CardName';

    public $incrementing = false;

    protected $fillable = ['CardName', 'EmployeeCode'];

    protected $appends = ['employee_name'];

    public function employee(){
        return $this->hasOne(\App\Employee::class, 'EmployeeCardname', 'CardName');
    }

    public function getEmployeeNameAttribute(){
        if($this->employee == null)
            return null;
        return $this->employee->EmployeeName;
    }

    public function getSupplierNameAttribute()
    {
        $user_id = $this->employee->SupplierName;
    }
}
