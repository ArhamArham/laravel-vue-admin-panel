<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::group(['middleware' => ['auth:api'], 'namespace'=>'Api'], function () {
    //Users
    Route::resource('users', 'UserController');
    Route::get('/verify', 'UserController@verify');
    Route::post('user/photo','UserController@changePhoto');
    Route::post('email/validate','UserController@verifyEmail');
    Route::post('user/role','UserController@changeRole');
    Route::post('users/delete','UserController@deleteAll');
    //Roles
    Route::resource('roles', 'RoleController');
    Route::post('roles/delete','RoleController@deleteAll');
    //Products
    Route::resource('products', 'ProductController');
    Route::post('product/photo','ProductController@changePhoto');
    //Categories
    Route::resource('categories', 'CategoriesController');
    Route::post('title/validate','CategoriesController@verifyTitle');
    //Settings
    Route::get('/admindetails', 'UserController@admindetails');
    Route::get('/settings', 'UserController@settings');
    Route::put('/updatesettings/{id}', 'UserController@updatesettings');
});
Route::post('login','Api\UserController@login')->name('login');