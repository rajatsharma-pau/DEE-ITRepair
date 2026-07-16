<?php
use Illuminate\Support\Facades\Route;

Route::get('/dee/login', 'DeeAuthController@showLogin')->name('dee.login');
Route::post('/dee/login', 'DeeAuthController@login')->name('dee.login.post');
Route::post('/dee/logout', 'DeeAuthController@logout')->name('dee.logout');

Route::group(['prefix'=>'dee','middleware'=>['auth:dee']], function(){
    Route::get('/dashboard', 'DeeDashboardController@index')->name('dee.dashboard');
    Route::get('/change-password', 'DeeAuthController@changePasswordForm')->name('dee.password.change');
    Route::post('/change-password', 'DeeAuthController@changePassword')->name('dee.password.update');

    Route::resource('repairs', 'DeeRepairRequestController', ['as'=>'dee']);
    Route::post('repairs/{id}/storekeeper-verify', 'DeeRepairRequestController@storekeeperVerify')->name('dee.repairs.storekeeper.verify')->middleware('dee.role:storekeeper,admin');
    Route::post('repairs/{id}/programmer-update', 'DeeRepairRequestController@programmerUpdate')->name('dee.repairs.programmer.update')->middleware('dee.role:programmer,admin');
    Route::post('repairs/{id}/employee-close', 'DeeRepairRequestController@employeeClose')->name('dee.repairs.employee.close');

    Route::resource('employees', 'DeeEmployeeController', ['as'=>'dee'])->middleware('dee.role:admin');
    Route::post('employees/{id}/promotions', 'DeeEmployeeController@addPromotion')->name('dee.employees.promotions.store')->middleware('dee.role:admin');
});
