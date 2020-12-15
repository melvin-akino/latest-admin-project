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

    //Provider accounts routes
    Route::get('/provider-accounts', 'ProviderAccountsController@index')->name('provider-accounts.api');
    Route::post('/provider-accounts/manage', 'ProviderAccountsController@manage')->name('provider-accounts-manage.api');

    //Providers routes
    Route::get('/providers', 'ProvidersController@index')->name('providers.api');
    //Routes to get all currencies
    Route::get('/currencies', 'CurrenciesController@index')->name('currencies.api');

    //Orders related routes
    Route::get('/orders', 'OrdersController@index')->name('orders.api');
    Route::get('/orders/open', 'OrdersController@getUserOpenOrders')->name('open-orders.api');
    Route::get('/orders/user', 'OrdersController@getUserTransactions')->name('orders-user.api');

    //System configurations related routes
    Route::get('/system-configurations', 'SystemConfigurationsController@index')->name('system-configurations.api');
    Route::post('/system-configurations/manage', 'SystemConfigurationsController@manage')->name('system-configurations-manage.api');

    //General Errors related routes
    Route::get('/general-errors', 'GeneralErrorMessagesController@index')->name('general-errors.api');
    Route::post('/general-errors/manage', 'GeneralErrorMessagesController@manage')->name('general-errors-manage.api');

    //Provider Errors related routes
    Route::get('/provider-errors', 'ProviderErrorMessagesController@index')->name('provider-errors.api');
    Route::post('/provider-errors/manage', 'ProviderErrorMessagesController@manage')->name('provider-errors-manage.api');

    //Customer related routes
    Route::get('/users', 'UsersController@index')->name('users.api');
    Route::post('/users/manage', 'UsersController@manage')->name('users-manage.api');
});
