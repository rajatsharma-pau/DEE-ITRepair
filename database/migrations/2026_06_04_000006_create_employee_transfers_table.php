<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeTransfersTable extends Migration
{
    public function up()
    {
        Schema::create('employee_transfers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('from_college_id')->nullable();
            $table->unsignedBigInteger('from_department_id')->nullable();
            $table->unsignedBigInteger('to_college_id');
            $table->unsignedBigInteger('to_department_id');
            $table->date('transfer_date');
            $table->date('relieving_date')->nullable();
            $table->date('joining_date')->nullable();
            $table->string('order_no')->nullable();
            $table->date('order_date')->nullable();
            $table->string('order_file')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('from_college_id')->references('id')->on('colleges')->onDelete('set null');
            $table->foreign('from_department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('to_college_id')->references('id')->on('colleges')->onDelete('cascade');
            $table->foreign('to_department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_transfers');
    }
}
