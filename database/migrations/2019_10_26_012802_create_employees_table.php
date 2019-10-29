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
            $table->increments('id');
            $table->string('EmployeeName');
            $table->string('EmployeeIdentity',50)->unique();
            $table->integer('user_id')->unsigned();
            $table->foreign('user_id')->unsigned();
                  ->references('id')
                  ->on('users')
                  ->onUpdate('cascade')
                  ->onDelete('cascade');
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
