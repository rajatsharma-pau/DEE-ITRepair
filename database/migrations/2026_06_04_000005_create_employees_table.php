<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->unsignedBigInteger('directorate_id')->nullable();
            $table->unsignedBigInteger('college_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('section_id')->nullable();
            $table->unsignedBigInteger('designation_id')->nullable();

            $table->string('employee_code')->nullable()->unique();
            $table->string('salutation', 20)->nullable();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('full_name')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();

            $table->string('gpf_no')->nullable();
            $table->string('nps_no')->nullable();
            $table->string('pan_no')->nullable();
            $table->string('aadhaar_no')->nullable();
            $table->string('salary_account_no')->nullable();
            $table->enum('job_type', ['Permanent', 'Adhoc', 'Temporary', 'Daily Wages'])->default('Permanent');

            $table->date('date_of_birth')->nullable();
            $table->date('date_of_joining')->nullable();
            $table->unsignedTinyInteger('retirement_age')->default(60);
            $table->date('calculated_retirement_date')->nullable();
            $table->date('manual_retirement_date')->nullable();
            $table->date('final_retirement_date')->nullable();

            $table->date('calculated_increment_date')->nullable();
            $table->date('manual_increment_date')->nullable();
            $table->date('final_increment_date')->nullable();
            $table->string('increment_remarks')->nullable();

            $table->string('manual_designation')->nullable();
            $table->string('photo')->nullable();
            $table->string('room_no')->nullable();

            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->unsignedBigInteger('country_id')->nullable();
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('city_id')->nullable();
            $table->string('manual_country')->nullable();
            $table->string('manual_state')->nullable();
            $table->string('manual_city')->nullable();
            $table->string('zip', 20)->nullable();

            $table->enum('status', ['Active', 'Retired', 'Transferred', 'Inactive'])->default('Active');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('directorate_id')->references('id')->on('directorates')->onDelete('set null');
            $table->foreign('college_id')->references('id')->on('colleges')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('section_id')->references('id')->on('sections')->onDelete('set null');
            $table->foreign('designation_id')->references('id')->on('designations')->onDelete('set null');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');
            $table->foreign('city_id')->references('id')->on('cities')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
