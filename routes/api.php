<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {

    return $request->user();
});

Route::apiResources(['user' => 'API\UserController', 'card' => 'API\CardController', 'employee' => 'API\EmployeeController', 'history' => 'API\HistoryController']);

Route::get('findUser', 'API\UserController@search');

Route::get('findCard', 'API\CardController@search');

Route::get('findEmployee', 'API\EmployeeController@search');

Route::get('getEmployeeOptions', 'API\CardController@getEmployeeOptions');

Route::get('getEmployeeOptionsEdit/{id}', 'API\CardController@getEmployeeOptionsEdit');

Route::get('getCardOptions', 'API\EmployeeController@getCardOptions');

Route::get('getCardOptionsEdit/{id}', 'API\EmployeeController@getCardOptionsEdit');
