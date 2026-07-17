<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddOtpPasswordFieldsToUsersTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'password_reset_otp')) {
                $table->string('password_reset_otp', 10)->nullable()->after('remember_token');
            }
            if (!Schema::hasColumn('users', 'password_reset_otp_expires_at')) {
                $table->timestamp('password_reset_otp_expires_at')->nullable()->after('password_reset_otp');
            }
            if (!Schema::hasColumn('users', 'password_reset_otp_verified_at')) {
                $table->timestamp('password_reset_otp_verified_at')->nullable()->after('password_reset_otp_expires_at');
            }
            if (!Schema::hasColumn('users', 'last_password_changed_at')) {
                $table->timestamp('last_password_changed_at')->nullable()->after('password_reset_otp_verified_at');
            }
            if (!Schema::hasColumn('users', 'must_change_password')) {
                $table->boolean('must_change_password')->default(false)->after('last_password_changed_at');
            }
        });
    }

    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'password_reset_otp')) {
                $table->dropColumn('password_reset_otp');
            }
            if (Schema::hasColumn('users', 'password_reset_otp_expires_at')) {
                $table->dropColumn('password_reset_otp_expires_at');
            }
            if (Schema::hasColumn('users', 'password_reset_otp_verified_at')) {
                $table->dropColumn('password_reset_otp_verified_at');
            }
            if (Schema::hasColumn('users', 'last_password_changed_at')) {
                $table->dropColumn('last_password_changed_at');
            }
            if (Schema::hasColumn('users', 'must_change_password')) {
                $table->dropColumn('must_change_password');
            }
        });
    }
}
