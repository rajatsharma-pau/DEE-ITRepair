<?php

namespace App\Http\Controllers\Masters;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

abstract class MasterBaseController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:superuser']);
    }

    protected function tableHasColumn($table, $column)
    {
        return Schema::hasTable($table) && Schema::hasColumn($table, $column);
    }

    protected function existsIn($table, $column, $value)
    {
        if (!$value || !$this->tableHasColumn($table, $column)) {
            return false;
        }

        return DB::table($table)->where($column, $value)->exists();
    }

    protected function deactivateOrDelete($model, array $usageChecks)
    {
        foreach ($usageChecks as $check) {
            if ($this->existsIn($check[0], $check[1], $model->id)) {
                if (Schema::hasColumn($model->getTable(), 'is_active')) {
                    $model->is_active = 0;
                    $model->save();

                    return [
                        'type' => 'warning',
                        'message' => 'This master record is already being used, so it has been deactivated instead of deleted. You may edit/rename it if required.',
                    ];
                }

                return [
                    'type' => 'warning',
                    'message' => 'This master record is already being used, so it cannot be deleted. You may edit/rename it if required.',
                ];
            }
        }

        $model->delete();

        return [
            'type' => 'success',
            'message' => 'Master record deleted successfully.',
        ];
    }
}
