<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssetsTable extends Migration
{
    public function up()
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('college_id')->nullable();
            $table->unsignedBigInteger('department_id')->nullable();
            $table->unsignedBigInteger('assigned_to_employee_id')->nullable();
            $table->string('asset_code')->nullable()->unique();
            $table->string('inventory_no')->nullable()->unique();
            $table->enum('asset_category', ['Computer','Printer','Scanner','UPS','Chair','Table','Sound System','Speaker','Webcam','Projector','Furniture','Electrical','Other'])->default('Other');
            $table->string('item_name');
            $table->string('make')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_no')->nullable();
            $table->string('configuration')->nullable();
            $table->string('location')->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('purchase_amount', 12, 2)->nullable();
            $table->string('purchase_order_no')->nullable();
            $table->date('warranty_till')->nullable();
            $table->enum('condition_status', ['Working','Needs Repair','Not Working','Obsolete','Condemned'])->default('Working');
            $table->enum('asset_state', ['In Store','With Employee','Under Repair','Returned to Store','Sent for Auction','Scrap/Auctioned','Lost'])->default('In Store');
            $table->date('state_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();
            $table->foreign('college_id')->references('id')->on('colleges')->onDelete('set null');
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('set null');
            $table->foreign('assigned_to_employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('assets');
    }
}
