<?php

Route::get('/', function () {
    return redirect()->route('login');
});

Auth::routes(['register' => false]);

Route::get('/home', 'HomeController@index')->name('home');
Route::get('/dashboard', 'HomeController@index')->name('dashboard');
Route::get('/pincode/{pincode}', 'PincodeController@lookup')->name('pincode.lookup');
Route::get('/departments/by-college/{college}', 'DepartmentController@byCollege')->name('departments.by-college');
Route::get('/repair-categories/{category}/problem-templates', 'RepairRequestController@problemTemplatesByCategory')->name('repair-categories.problem-templates');

Route::group(['middleware' => ['auth']], function () {
    Route::get('profile', 'ProfileController@show')->name('profile.show');
    Route::post('profile/photo', 'ProfileController@updatePhoto')->name('profile.photo.update');
    Route::delete('profile/photo', 'ProfileController@removePhoto')->name('profile.photo.remove');

    Route::resource('employees', 'EmployeeController');
    Route::post('employees/{employee}/service-movements', 'EmployeeServiceMovementController@store')->name('employees.service-movements.store');
    Route::delete('employees/{employee}/service-movements/{movement}', 'EmployeeServiceMovementController@destroy')->name('employees.service-movements.destroy');
    Route::post('employees/{employee}/charges', 'EmployeeChargeController@store')->name('employees.charges.store');
    Route::delete('employees/{employee}/charges/{charge}', 'EmployeeChargeController@destroy')->name('employees.charges.destroy');
    Route::post('employees/{employee}/transfers', 'EmployeeTransferController@store')->name('employees.transfers.store');
    Route::delete('employees/{employee}/transfers/{transfer}', 'EmployeeTransferController@destroy')->name('employees.transfers.destroy');

    Route::resource('repair-requests', 'RepairRequestController');
    Route::post('repair-requests/{repair_request}/storekeeper-action', 'RepairRequestController@storekeeperAction')->name('repair-requests.storekeeper-action');
    Route::post('repair-requests/{repair_request}/programmer-action', 'RepairRequestController@programmerAction')->name('repair-requests.programmer-action');
    Route::post('repair-requests/{repair_request}/d4-action', 'RepairRequestController@d4Action')->name('repair-requests.d4-action');
    Route::post('repair-requests/{repair_request}/status', 'RepairRequestController@updateStatus')->name('repair-requests.status');
    Route::post('repair-requests/{repair_request}/feedback', 'RepairRequestController@employeeFeedback')->name('repair-requests.feedback');
    Route::get('repair-requests/{repair_request}/proforma', 'RepairRequestController@proforma')->name('repair-requests.proforma');


    Route::get('assets/by-employee/{employee}', 'AssetController@byEmployee')->name('assets.by-employee');
    Route::get('assets/{asset}/json', 'AssetController@assetJson')->name('assets.json');
    Route::resource('assets', 'AssetController');
    Route::post('assets/{asset}/history', 'AssetController@addHistory')->name('assets.history.store');

    Route::resource('store-items', 'StoreItemController');
    Route::post('store-items/{store_item}/adjust-stock', 'StoreItemController@adjustStock')->name('store-items.adjust-stock');

    Route::resource('store-indents', 'StoreIndentController')->except(['edit','update','destroy']);
    Route::post('store-indents/{store_indent}/issue', 'StoreIndentController@issue')->name('store-indents.issue');
    Route::post('store-indents/{store_indent}/reject', 'StoreIndentController@reject')->name('store-indents.reject');

    Route::get('masters/colleges', 'MasterDataController@colleges')->name('masters.colleges');
    Route::post('masters/colleges', 'MasterDataController@storeCollege')->name('masters.colleges.store');
    Route::get('masters/departments', 'MasterDataController@departments')->name('masters.departments');
    Route::post('masters/departments', 'MasterDataController@storeDepartment')->name('masters.departments.store');
    Route::get('masters/designations', 'MasterDataController@designations')->name('masters.designations');
    Route::post('masters/designations', 'MasterDataController@storeDesignation')->name('masters.designations.store');
    Route::get('masters/sections', 'MasterDataController@sections')->name('masters.sections');
    Route::post('masters/sections', 'MasterDataController@storeSection')->name('masters.sections.store');
    Route::get('masters/vendors', 'MasterDataController@vendors')->name('masters.vendors');
    Route::post('masters/vendors', 'MasterDataController@storeVendor')->name('masters.vendors.store');
    Route::get('masters/repair-categories', 'MasterDataController@repairCategories')->name('masters.repair-categories');
    Route::post('masters/repair-categories', 'MasterDataController@storeRepairCategory')->name('masters.repair-categories.store');
    Route::get('masters/problem-templates', 'MasterDataController@problemTemplates')->name('masters.problem-templates');
    Route::post('masters/problem-templates', 'MasterDataController@storeProblemTemplate')->name('masters.problem-templates.store');
    Route::get('masters/routing-rules', 'MasterDataController@routingRules')->name('masters.routing-rules');
    Route::post('masters/routing-rules', 'MasterDataController@storeRoutingRule')->name('masters.routing-rules.store');
});
