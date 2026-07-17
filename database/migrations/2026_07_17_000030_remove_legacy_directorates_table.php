<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveLegacyDirectoratesTable extends Migration
{
    public function up()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');

        if (Schema::hasTable('sections')) {
            Schema::table('sections', function (Blueprint $table) {
                if (!Schema::hasColumn('sections', 'college_id')) {
                    $table->unsignedBigInteger('college_id')->nullable()->after('id');
                }
                if (!Schema::hasColumn('sections', 'department_id')) {
                    $table->unsignedBigInteger('department_id')->nullable()->after('college_id');
                }
            });

            $deeCollegeId = DB::table('colleges')->where('name', 'Directorate of Extension Education')->value('id');
            $deeDepartmentId = null;
            if ($deeCollegeId) {
                $deeDepartmentId = DB::table('departments')
                    ->where('college_id', $deeCollegeId)
                    ->where('name', 'Directorate of Extension Education')
                    ->value('id');
            }

            if ($deeCollegeId) {
                DB::table('sections')->whereNull('college_id')->update(['college_id' => $deeCollegeId]);
            }
            if ($deeDepartmentId) {
                DB::table('sections')->whereNull('department_id')->update(['department_id' => $deeDepartmentId]);
            }

            if (Schema::hasColumn('sections', 'directorate_id')) {
                try { Schema::table('sections', function (Blueprint $table) { $table->dropForeign(['directorate_id']); }); } catch (\Exception $e) {}
                Schema::table('sections', function (Blueprint $table) { $table->dropColumn('directorate_id'); });
            }
        }

        foreach (['employees', 'assets', 'store_items'] as $tableName) {
            if (Schema::hasTable($tableName) && Schema::hasColumn($tableName, 'directorate_id')) {
                try {
                    Schema::table($tableName, function (Blueprint $table) { $table->dropForeign(['directorate_id']); });
                } catch (\Exception $e) {}
                Schema::table($tableName, function (Blueprint $table) use ($tableName) {
                    if (Schema::hasColumn($tableName, 'directorate_id')) {
                        $table->dropColumn('directorate_id');
                    }
                });
            }
        }

        if (Schema::hasTable('directorates')) {
            Schema::drop('directorates');
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function down()
    {
        // Not re-creating the legacy directorates table. College / Directorate is now handled by colleges.
    }
}
