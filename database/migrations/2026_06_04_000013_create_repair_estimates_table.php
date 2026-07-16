<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRepairEstimatesTable extends Migration
{
    public function up()
    {
        Schema::create('repair_estimates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('repair_request_id');
            $table->unsignedBigInteger('vendor_id');
            $table->decimal('estimate_amount', 12, 2);
            $table->date('estimate_date')->nullable();
            $table->text('estimate_details')->nullable();
            $table->string('estimate_file')->nullable();
            $table->boolean('is_selected')->default(false);
            $table->unsignedBigInteger('entered_by')->nullable();
            $table->unsignedBigInteger('programmer_verified_by')->nullable();
            $table->enum('programmer_verification_status', ['Pending', 'Estimate OK', 'Estimate Not OK', 'Need Revised Estimate'])->default('Pending');
            $table->text('programmer_remarks')->nullable();
            $table->timestamp('programmer_verified_at')->nullable();
            $table->timestamps();

            $table->foreign('repair_request_id')->references('id')->on('repair_requests')->onDelete('cascade');
            $table->foreign('vendor_id')->references('id')->on('vendors')->onDelete('restrict');
            $table->foreign('entered_by')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('programmer_verified_by')->references('id')->on('employees')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('repair_estimates');
    }
}
