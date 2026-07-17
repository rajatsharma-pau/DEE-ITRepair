<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class FixPasswordResetOtpColumnLength extends Migration
{
    public function up()
    {
        // bcrypt/Hash::make() value is about 60 characters, so keep this column wide enough.
        // DB::statement is used to avoid needing doctrine/dbal for Laravel 5.8 column changes.
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'password_reset_otp')) {
            DB::statement("ALTER TABLE users MODIFY password_reset_otp VARCHAR(255) NULL");
        }

        if (Schema::hasTable('users') && Schema::hasColumn('users', 'password_reset_otp_expires_at')) {
            DB::statement("ALTER TABLE users MODIFY password_reset_otp_expires_at DATETIME NULL");
        }
    }

    public function down()
    {
        if (Schema::hasTable('users') && Schema::hasColumn('users', 'password_reset_otp')) {
            DB::statement("ALTER TABLE users MODIFY password_reset_otp VARCHAR(10) NULL");
        }
    }
}
