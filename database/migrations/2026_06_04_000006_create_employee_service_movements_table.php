<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeServiceMovementsTable extends Migration
{
    public function up()
    {
        Schema::create('employee_service_movements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('employee_id');
            $table->enum('movement_type', ['Joining', 'Promotion', 'Transfer', 'Additional Charge', 'Reversion', 'Retirement', 'Resignation', 'Contract Extension', 'Other'])->default('Promotion');
            $table->unsignedBigInteger('from_designation_id')->nullable();
            $table->unsignedBigInteger('to_designation_id')->nullable();
            $table->string('manual_from_designation')->nullable();
            $table->string('manual_to_designation')->nullable();
            $table->date('effective_date');
            $table->string('order_no')->nullable();
            $table->date('order_date')->nullable();
            $table->string('document_path')->nullable();
            $table->text('remarks')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();

            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->foreign('from_designation_id')->references('id')->on('designations')->onDelete('set null');
            $table->foreign('to_designation_id')->references('id')->on('designations')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employee_service_movements');
    }
}
