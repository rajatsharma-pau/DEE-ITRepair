<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreIndentsTable extends Migration
{
    public function up()
    {
        Schema::create('store_indents', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('indent_no')->unique();
            $table->unsignedBigInteger('employee_id');
            $table->unsignedBigInteger('college_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('issued_by_employee_id')->nullable();
            $table->enum('status', ['Submitted','Issued','Partially Issued','Rejected','Cancelled'])->default('Submitted');
            $table->date('required_date')->nullable();
            $table->date('issued_date')->nullable();
            $table->text('employee_remarks')->nullable();
            $table->text('storekeeper_remarks')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('college_id')->references('id')->on('colleges')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('issued_by_employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('store_indents');
    }
}
