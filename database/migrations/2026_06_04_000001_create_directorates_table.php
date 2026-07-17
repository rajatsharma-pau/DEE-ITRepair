<?php

use Illuminate\Database\Migrations\Migration;

class CreateDirectoratesTable extends Migration
{
    public function up()
    {
        // Legacy migration intentionally left empty.
        // College / Directorate values are stored in the colleges table.
        // Departments / Offices / KVKs are stored in the departments table.
    }

    public function down()
    {
        // Nothing to drop.
    }
}
