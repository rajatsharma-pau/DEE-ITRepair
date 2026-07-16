<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRepairRoutingRulesTable extends Migration
{
    public function up()
    {
        Schema::create('repair_routing_rules', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('repair_category_id');
            $table->enum('handler_type', ['role', 'charge', 'employee'])->default('role');
            $table->string('handler_value')->nullable(); // programmer/storekeeper or Store Incharge
            $table->unsignedBigInteger('handler_employee_id')->nullable();
            $table->boolean('requires_store_verification')->default(false);
            $table->boolean('requires_store_incharge_approval')->default(false);
            $table->boolean('requires_programmer_verification')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->foreign('repair_category_id')->references('id')->on('repair_categories')->onDelete('cascade');
            $table->foreign('handler_employee_id')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('repair_routing_rules');
    }
}
