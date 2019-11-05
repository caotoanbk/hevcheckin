<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->string('EmployeeCode')->primary();
            $table->string('EmployeeName');
            // $table->string('EmployeeIdentity',50)->unique();
            $table->string('SupplierName')->nullable();
            // $table->foreign('SupplierName')
            //       ->references('SupplierName')
            //       ->on('suppliers')
            //       ->onUpdate('cascade')
            //       ->onDelete('cascade');
            $table->string('EmployeePhoto')->default('employee.png');
            $table->string('EmployeeType', 50)->default('Temporary Worker');
            $table->string('EmployeeCardname', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
