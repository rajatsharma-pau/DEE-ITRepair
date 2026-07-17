<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class EnsurePhoneLoginForDeeEmployees extends Migration
{
    public function up()
    {
        if (Schema::hasTable('users')) {
            Schema::table('users', function (Blueprint $table) {
                if (!Schema::hasColumn('users', 'phone')) {
                    $table->string('phone', 20)->nullable()->after('email')->index();
                }
                if (!Schema::hasColumn('users', 'is_active')) {
                    $table->boolean('is_active')->default(1)->after('password');
                }
                if (!Schema::hasColumn('users', 'must_change_password')) {
                    $table->boolean('must_change_password')->default(1)->after('is_active');
                }
                if (!Schema::hasColumn('users', 'role')) {
                    $table->string('role', 50)->default('employee')->after('password');
                }
            });
        }
    }

    public function down()
    {
        // Do not drop live login columns.
    }
}
