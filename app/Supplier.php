<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    protected $primaryKey = 'SupplierName';
    public $incrementing = false;
    protected $fillable = ['SupplierName', 'SupplierInfo', 'SupplierCardRange'];
}
