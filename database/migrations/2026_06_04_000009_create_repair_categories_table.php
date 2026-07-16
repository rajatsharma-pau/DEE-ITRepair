<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateRepairCategoriesTable extends Migration
{
    public function up()
    {
        Schema::create('repair_categories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name');
            $table->enum('item_group', ['Computer Related', 'Non Computer', 'General'])->default('Computer Related');
            $table->enum('default_handler', ['programmer', 'storekeeper', 'store_incharge', 'director'])->default('programmer');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('repair_categories');
    }
}
