<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->route('home');
});

Route::middleware('auth')->get('/current-user', function (Request $request) {

   return \App\User::with('supplier')->find(auth()->id());

});

Auth::routes();

Route::get('/dashboard', 'HomeController@index')->name('home');

Route::get('{path}', 'HomeController@index')->where('path', '([-A-z\d\/_.]+)?' );
