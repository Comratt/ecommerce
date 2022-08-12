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

Route::group(['prefix' =>     'auth'], function() {
    Route::post('/login',                   'AuthController@login');
    Route::post('/signup',                  'AuthController@signup');
});


Route::group(['prefix' => 'admin'], function() {
    // /*BANNER
    Route::get('/banners',                  'BannerController@index');
    Route::get('/banners/{id}',             'BannerController@show');
    // CATEGORY
    Route::get('/categories',               'CategoryController@index');
    Route::get('/categories/{id}',          'CategoryController@show');

    // PRODUCTS
    Route::get('/products',                 'ProductController@index');
    Route::get('/product/models',                 'ProductController@listModels');
    Route::get('/products/{id}',            'ProductController@show');
    Route::get('/product/price',            'ProductController@minMaxPrice');
    Route::get('/product/colors',            'ProductController@colors');
    Route::get('/product/sizes',            'ProductController@sizes');

    // OPTIONS
    Route::get('/options',                 'OptionController@index');
    Route::get('/options/{id}',            'OptionController@show');
    Route::get('/options-value',           'OptionValueController@index');

    // ORDERS
    Route::post('/orders', 'OrderController@store');
    Route::post('/get/orders/email', 'OrderController@getByEmail');

    //PROMOCODES
    Route::post('/promocodes/get', 'PromoController@getByName');

    Route::group(['middleware' => ['auth:api','admin']], function (){
        //ANALYTICS
        Route::get('/analytics', 'ProductController@getAnalytics');
        Route::get('/analytics/orders', 'ProductController@getAnalyticsOrders');
        Route::get('/analytics/categories', 'ProductController@getAnalyticsCategories');
        Route::get('/analytics/products', 'ProductController@getAnalyticsProducts');
    });
    Route::group(['middleware' => ['auth:api','subadmin']], function (){
        // BANNERS
        Route::post('/banners',                 'BannerController@store');
        Route::post('/banners/{id}/edit',       'BannerController@update');
        Route::delete('/banners/{id}',          'BannerController@destroy');
        // CATEGORIES
        Route::post('/categories',              'CategoryController@store');
        Route::post('/categories/{id}/edit',    'CategoryController@update');
        Route::delete('/categories/{id}',       'CategoryController@destroy');
        // PRODUCTS
        Route::get('/product/generate',         'ProductController@generate');
        Route::post('/products',                'ProductController@store');
        Route::post('/products/{id}/edit',      'ProductController@update');
        // OPTIONS
        Route::post('/options',                'OptionValueController@store');
        Route::post('/options/{id}/edit',      'OptionController@update');
        Route::delete('/options/{id}',         'OptionController@destroy');
        Route::delete('/options-value/{id}',   'OptionValueController@destroy');
        // ORDERS
        Route::get('/orders', 'OrderController@index');
        Route::get('/orders/{id}', 'OrderController@show');
        Route::post('/orders-history', 'OrderController@addHistory');
        // RETURNS
        Route::post('/return/{id}', 'ReturnController@store');
        //CUSTOMERS
        Route::get('/customers', 'AuthController@getAllCustomers');
        Route::post('/customers/update', 'AuthController@modifyUser');
        // PROMOCODES
        Route::get('/promocodes', 'PromoController@showAll');
        Route::post('/promocodes', 'PromoController@store');
        Route::post('/promocodes/{id}/edit', 'PromoController@update');
        Route::delete('/promocodes/{id}', 'PromoController@delete');
    });
});
