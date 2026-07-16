<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProblemTemplatesTable extends Migration
{
    public function up()
    {
        Schema::create('problem_templates', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('repair_category_id')->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('item_group')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->foreign('repair_category_id')->references('id')->on('repair_categories')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('problem_templates');
    }
}
