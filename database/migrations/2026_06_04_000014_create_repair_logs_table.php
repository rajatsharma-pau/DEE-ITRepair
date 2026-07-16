<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRepairLogsTable extends Migration
{
    public function up()
    {
        Schema::create('repair_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('repair_request_id');
            $table->unsignedBigInteger('action_by')->nullable();
            $table->string('action');
            $table->string('old_status')->nullable();
            $table->string('new_status')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('repair_request_id')->references('id')->on('repair_requests')->onDelete('cascade');
            $table->foreign('action_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('repair_logs');
    }
}
