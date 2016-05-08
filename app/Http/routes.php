<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::auth();

Route::get('/home', 'HomeController@index');

Route::get('/2fa/enable', 'Google2FAController@generateSecret');
Route::get('/2fa/disable', 'Google2FAController@removeSecret');
Route::get('/2fa/validate', 'Auth\AuthController@getValidateSecret');
Route::post('/2fa/validate', ['middleware' => 'throttle', 'uses' => 'Auth\AuthController@postValidateSecret']);
