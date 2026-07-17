<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EnsureMasterActiveColumns extends Migration
{
    public function up()
    {
        foreach (['colleges', 'departments', 'designations'] as $table) {
            if (Schema::hasTable($table) && !Schema::hasColumn($table, 'is_active')) {
                Schema::table($table, function (Blueprint $table) {
                    $table->boolean('is_active')->default(1)->after('name');
                });
            }
        }
    }

    public function down()
    {
        foreach (['colleges', 'departments', 'designations'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'is_active')) {
                Schema::table($tableName, function (Blueprint $table) {
                    $table->dropColumn('is_active');
                });
            }
        }
    }
}
