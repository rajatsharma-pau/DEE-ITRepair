<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStoreStockMovementsTable extends Migration
{
    public function up()
    {
        Schema::create('store_stock_movements', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('store_item_id');
            $table->unsignedBigInteger('store_indent_id')->nullable();
            $table->unsignedBigInteger('employee_id')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->enum('movement_type', ['Opening','Stock In','Issue','Return','Adjustment'])->default('Adjustment');
            $table->decimal('quantity', 12, 2);
            $table->decimal('balance_after', 12, 2)->default(0);
            $table->date('movement_date')->nullable();
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('store_item_id')->references('id')->on('store_items')->onDelete('cascade');
            $table->foreign('store_indent_id')->references('id')->on('store_indents')->onDelete('set null');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('set null');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('store_stock_movements');
    }
}
