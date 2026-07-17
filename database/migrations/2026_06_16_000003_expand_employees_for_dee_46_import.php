<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class ExpandEmployeesForDee46Import extends Migration
{
    /**
     * Run the migrations.
     *
     * This migration is intentionally defensive because the DEE project has
     * gone through many patches. It only adds columns that are missing and
     * converts job_type to VARCHAR so values like Permanent, Adhoc,
     * Temporary and Daily Wages can be stored safely.
     */
    public function up()
    {
        if (!Schema::hasTable('employees')) {
            return;
        }

        // Change job_type from enum to string if it already exists.
        if (Schema::hasColumn('employees', 'job_type')) {
            try {
                DB::statement("ALTER TABLE employees MODIFY job_type VARCHAR(50) NULL");
            } catch (Exception $e) {
                // Ignore if database does not allow modify in current state.
            }
        }

        Schema::table('employees', function (Blueprint $table) {
            if (!Schema::hasColumn('employees', 'employee_code')) {
                $table->string('employee_code', 100)->nullable()->index()->after('id');
            }
            if (!Schema::hasColumn('employees', 'pf_no')) {
                $table->string('pf_no', 100)->nullable()->index()->after('employee_code');
            }
            if (!Schema::hasColumn('employees', 'gpf_no')) {
                $table->string('gpf_no', 100)->nullable()->index()->after('pf_no');
            }
            if (!Schema::hasColumn('employees', 'nps_no')) {
                $table->string('nps_no', 100)->nullable()->index()->after('gpf_no');
            }
            if (!Schema::hasColumn('employees', 'pan_no')) {
                $table->string('pan_no', 20)->nullable()->index()->after('nps_no');
            }
            if (!Schema::hasColumn('employees', 'aadhaar_no')) {
                $table->string('aadhaar_no', 20)->nullable()->after('pan_no');
            }
            if (!Schema::hasColumn('employees', 'salary_account_no')) {
                $table->string('salary_account_no', 100)->nullable()->after('aadhaar_no');
            }
            if (!Schema::hasColumn('employees', 'mobile_no')) {
                $table->string('mobile_no', 20)->nullable()->index()->after('salary_account_no');
            }
            if (!Schema::hasColumn('employees', 'alternate_mobile_no')) {
                $table->string('alternate_mobile_no', 20)->nullable()->after('mobile_no');
            }
            if (!Schema::hasColumn('employees', 'email')) {
                $table->string('email')->nullable()->after('alternate_mobile_no');
            }
            if (!Schema::hasColumn('employees', 'gender')) {
                $table->string('gender', 20)->nullable()->after('email');
            }
            if (!Schema::hasColumn('employees', 'father_name')) {
                $table->string('father_name')->nullable()->after('gender');
            }
            if (!Schema::hasColumn('employees', 'qualification')) {
                $table->text('qualification')->nullable()->after('father_name');
            }
            if (!Schema::hasColumn('employees', 'pay_scale')) {
                $table->string('pay_scale')->nullable()->after('qualification');
            }
            if (!Schema::hasColumn('employees', 'basic_pay')) {
                $table->decimal('basic_pay', 12, 2)->nullable()->after('pay_scale');
            }
            if (!Schema::hasColumn('employees', 'grade_pay')) {
                $table->decimal('grade_pay', 12, 2)->nullable()->after('basic_pay');
            }
            if (!Schema::hasColumn('employees', 'increment_month')) {
                $table->string('increment_month', 30)->nullable()->after('grade_pay');
            }
            if (!Schema::hasColumn('employees', 'last_promotion_designation')) {
                $table->string('last_promotion_designation')->nullable()->after('increment_month');
            }
            if (!Schema::hasColumn('employees', 'last_promotion_date')) {
                $table->date('last_promotion_date')->nullable()->after('last_promotion_designation');
            }
            if (!Schema::hasColumn('employees', 'category')) {
                $table->string('category', 100)->nullable()->after('last_promotion_date');
            }
            if (!Schema::hasColumn('employees', 'blood_group')) {
                $table->string('blood_group', 20)->nullable()->after('category');
            }
            if (!Schema::hasColumn('employees', 'election_duty')) {
                $table->string('election_duty', 50)->nullable()->after('blood_group');
            }
            if (!Schema::hasColumn('employees', 'blo_duty')) {
                $table->string('blo_duty', 50)->nullable()->after('election_duty');
            }
            if (!Schema::hasColumn('employees', 'net_qualified')) {
                $table->string('net_qualified', 50)->nullable()->after('blo_duty');
            }
            if (!Schema::hasColumn('employees', 'gate_qualified')) {
                $table->string('gate_qualified', 50)->nullable()->after('net_qualified');
            }
            if (!Schema::hasColumn('employees', 'remarks')) {
                $table->text('remarks')->nullable()->after('gate_qualified');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        if (!Schema::hasTable('employees')) {
            return;
        }

        Schema::table('employees', function (Blueprint $table) {
            $columns = [
                'employee_code', 'pf_no', 'gpf_no', 'nps_no', 'pan_no', 'aadhaar_no',
                'salary_account_no', 'mobile_no', 'alternate_mobile_no', 'email', 'gender',
                'father_name', 'qualification', 'pay_scale', 'basic_pay', 'grade_pay',
                'increment_month', 'last_promotion_designation', 'last_promotion_date',
                'category', 'blood_group', 'election_duty', 'blo_duty', 'net_qualified',
                'gate_qualified', 'remarks'
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('employees', $column)) {
                    try {
                        $table->dropColumn($column);
                    } catch (Exception $e) {
                        // Ignore drop failure in rollback for patched installations.
                    }
                }
            }
        });
    }
}
