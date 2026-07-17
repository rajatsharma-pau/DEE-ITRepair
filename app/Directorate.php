<?php
namespace App;

/**
 * Backward-compatibility alias only.
 * There is no separate `directorates` table now.
 * College / Directorate records are stored in the `colleges` table.
 */
class Directorate extends College
{
    protected $table = 'colleges';
}
