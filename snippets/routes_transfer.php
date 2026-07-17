// Add inside auth middleware group in routes/web.php:
Route::get('employees/{employee}/transfer', 'EmployeeTransferController@create')->name('employees.transfer.create');
Route::post('employees/{employee}/transfer', 'EmployeeTransferController@store')->name('employees.transfer.store');
