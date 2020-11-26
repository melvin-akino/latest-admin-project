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
Route::group(['middleware' => ['cors', 'json.response']], function () {
    // public routes
    Route::post('/login', 'Auth\ApiAuthController@login')->name('login.api');
    Route::post('/create','Auth\ApiAuthController@create')->name('create.api');
});
Route::middleware('auth:api')->group(function () {
    // our routes to be protected will go in here
    Route::post('/logout', 'Auth\ApiAuthController@logout')->name('logout.api');

    Route::get('/provider_accounts', 'ProviderAccountsController@index')->name('provider_accounts.api');
    Route::post('/provider_accounts/manage', 'ProviderAccountsController@manage')->name('provider_accounts_manage.api');

    //Routes to get all currencies
    Route::get('/currencies', 'CurrenciesController@index')->name('currencies.api');
});
