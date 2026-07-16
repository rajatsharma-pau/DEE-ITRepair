<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAssetHistoriesTable extends Migration
{
    public function up()
    {
        Schema::create('asset_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('asset_id');
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('action_by')->nullable();
            $table->enum('action_type', ['Created','Assigned','Returned to Store','Sent for Repair','Repair Completed','Sent for Auction','Scrap/Auctioned','Lost','Status Updated','Remarks Added'])->default('Status Updated');
            $table->string('from_state')->nullable();
            $table->string('to_state')->nullable();
            $table->date('action_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('asset_id')->references('id')->on('assets')->onDelete('cascade');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('action_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('asset_histories');
    }
}
