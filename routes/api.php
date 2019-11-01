<?php

use Illuminate\Http\Request;

// Route::middleware('auth')->get('/user', function (Request $request) {

//     return $request->user();
// });

Route::apiResources([
    // 'user' => 'API\UserController', 
    'card' => 'API\CardController', 
    'employee' => 'API\EmployeeController', 
    'history' => 'API\HistoryController',
    'supplier' => 'API\SupplierController'
]);

Route::get('findSupplier', 'API\SupplierController@search');

Route::get('findCard', 'API\CardController@search');

Route::get('findEmployee', 'API\EmployeeController@search');

Route::get('findHistory', 'API\HistoryController@search');




Route::get('getEmployeeOptions', 'API\CardController@getEmployeeOptions');

Route::get('getEmployeeOptionsEdit/{id}', 'API\CardController@getEmployeeOptionsEdit');

Route::get('getCardOptions', 'API\EmployeeController@getCardOptions');

Route::get('getCardOptionsEdit/{id}', 'API\EmployeeController@getCardOptionsEdit');

// Route::get('get-supplier', 'API\EmployeeController@getSuppliers');
