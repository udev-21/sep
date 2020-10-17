<?php

use Illuminate\Support\Facades\Route;

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
    return view('welcome');
});
Route::get('/test',"MainController@update");
Route::get('/search', 'MainController@search')->name('search');

Route::get('/status', 'MainController@status')->name('status');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');
