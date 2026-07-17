<?php

use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'role:superuser'])->prefix('master')->name('master.')->group(function () {
    Route::resource('colleges', 'Masters\CollegeMasterController');
    Route::resource('departments', 'Masters\DepartmentMasterController');
    Route::resource('designations', 'Masters\DesignationMasterController');
});
