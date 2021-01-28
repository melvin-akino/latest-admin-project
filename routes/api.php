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
});
Route::group(['middleware' => ['auth:api', 'admin.active']], function () {
    // our routes to be protected will go in here
    Route::post('/logout', 'Auth\ApiAuthController@logout')->name('logout.api');
    Route::post('/admin-users/manage','AdminUsersController@manage')->name('admin-user-manage.api');
    Route::get('/admin-users','AdminUsersController@index')->name('admin-users.api');
    Route::get('/admin-users/logs','AdminUsersController@getAdminActivityLogs')->name('admin-logs.api');
    Route::get('/admin-user/{id}','AdminUsersController@getAdminUser')->name('admin-user.api');

    //Provider accounts routes
    Route::get('/provider-accounts', 'ProviderAccountsController@index')->name('provider-accounts.api');
    Route::post('/provider-accounts/manage', 'ProviderAccountsController@manage')->name('provider-accounts-manage.api');
    Route::get('/provider-accounts/orders', 'ProviderTransactionsController@transactions')->name('provider-transactions.api');
    Route::get('/provider-account/uuid/{uuid}', 'ProviderAccountsController@getProviderAccountByUuid')->name('get-provider-account-by-uuid.api');


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
    Route::get('/user/{id}', 'UsersController@getUser')->name('get-user-by-id.api');
    Route::get('/user/uuid/{uuid}', 'UsersController@getUserByUuid')->name('get-user-by-uuid.api');

    //Wallet replated routes
    Route::get('/users/wallet', 'WalletsController@getUserBalance')->name('users-wallet.api');
    Route::post('/wallet/token', 'WalletsController@getAccessToken')->name('wallet-token.api');
    Route::get('/wallet/clients', 'WalletsController@getClients')->name('wallet-clients.api');
    Route::post('/wallet/create', 'WalletsController@createClient')->name('wallet-create.api');
    Route::post('/wallet/revoke', 'WalletsController@revokeClient')->name('wallet-revoke.api');
    Route::get('/wallet/currencies', 'WalletsController@getCurrencies')->name('wallet-currencies.api');
    Route::post('/wallet/currencies/create', 'WalletsController@createCurrency')->name('wallet-currencies-create.api');
    Route::post('/wallet/currencies/update', 'WalletsController@updateCurrency')->name('wallet-currencies-create.api');
    Route::post('/wallet/update', 'WalletsController@walletUpdate')->name('wallet-update.api');
    Route::get('/wallet/balance', 'WalletsController@walletBalance')->name('wallet-balance.api');

    //Admin Settlement related routes
    Route::post('/settlements/create', 'AdminSettlementsController@create')->name('settlement-create.api');

    //Wallet related routes
    Route::get('/wallet/transaction', 'WalletsController@walletTransaction')->name('wallet-transaction.api');
});
