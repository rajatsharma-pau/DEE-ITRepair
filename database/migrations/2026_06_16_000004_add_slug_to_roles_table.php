<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AddSlugToRolesTable extends Migration
{
    public function up()
    {
        Schema::table('roles', function (Blueprint $table) {
            if (!Schema::hasColumn('roles', 'slug')) {
                $table->string('slug')->nullable()->after('name');
            }

            if (!Schema::hasColumn('roles', 'display_name')) {
                $table->string('display_name', 100)->nullable()->after('slug');
            }

            if (!Schema::hasColumn('roles', 'description')) {
                $table->string('description')->nullable()->after('display_name');
            }

            if (!Schema::hasColumn('roles', 'is_active')) {
                $table->boolean('is_active')->default(1)->after('description');
            }
        });

        $now = Carbon::now();

        $roles = [
            ['name' => 'Superuser',        'slug' => 'superuser',        'display_name' => 'Superuser',        'description' => 'University-level full control'],
            ['name' => 'Admin',            'slug' => 'admin',            'display_name' => 'Admin',            'description' => 'General admin'],
            ['name' => 'College Admin',    'slug' => 'college_admin',    'display_name' => 'College Admin',    'description' => 'College/directorate-level admin'],
            ['name' => 'Department Admin', 'slug' => 'department_admin', 'display_name' => 'Department Admin', 'description' => 'Department/KVK/office-level admin'],
            ['name' => 'Employee',         'slug' => 'employee',         'display_name' => 'Employee',         'description' => 'Can submit requests and view own records'],
            ['name' => 'Storekeeper',      'slug' => 'storekeeper',      'display_name' => 'Storekeeper',      'description' => 'Handles assets, estimates, store stock and indents'],
            ['name' => 'Programmer',       'slug' => 'programmer',       'display_name' => 'Programmer',       'description' => 'Technical verification for computer-related work'],
            ['name' => 'Store Incharge',   'slug' => 'store_incharge',   'display_name' => 'Store Incharge',   'description' => 'Verification for store/non-computer related work'],
            ['name' => 'D-4 Seat',         'slug' => 'd4_seat',          'display_name' => 'D-4 Seat',         'description' => 'Manual financial file tracking'],
            ['name' => 'Director',         'slug' => 'director',         'display_name' => 'Director / Head',  'description' => 'College/directorate viewing and approval role'],
        ];

        foreach ($roles as $role) {
            $existing = DB::table('roles')
                ->where('slug', $role['slug'])
                ->orWhere('name', $role['name'])
                ->first();

            if ($existing) {
                DB::table('roles')->where('id', $existing->id)->update([
                    'name' => $role['name'],
                    'slug' => $role['slug'],
                    'display_name' => $role['display_name'],
                    'description' => $role['description'],
                    'is_active' => 1,
                    'updated_at' => $now,
                ]);
            } else {
                DB::table('roles')->insert([
                    'name' => $role['name'],
                    'slug' => $role['slug'],
                    'display_name' => $role['display_name'],
                    'description' => $role['description'],
                    'is_active' => 1,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    public function down()
    {
        // Do not delete seeded roles here. Other tables may reference them.
        Schema::table('roles', function (Blueprint $table) {
            if (Schema::hasColumn('roles', 'slug')) {
                $table->dropColumn('slug');
            }
        });
    }
}
