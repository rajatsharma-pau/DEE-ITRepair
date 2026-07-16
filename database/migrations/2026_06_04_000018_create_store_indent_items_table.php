<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreIndentItemsTable extends Migration
{
    public function up()
    {
        Schema::create('store_indent_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('store_indent_id');
            $table->unsignedBigInteger('store_item_id');
            $table->decimal('requested_qty', 12, 2)->default(0);
            $table->decimal('approved_qty', 12, 2)->nullable();
            $table->decimal('issued_qty', 12, 2)->default(0);
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('store_indent_id')->references('id')->on('store_indents')->onDelete('cascade');
            $table->foreign('store_item_id')->references('id')->on('store_items')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('store_indent_items');
    }
}
