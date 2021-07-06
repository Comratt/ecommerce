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

// PRODUCTS
Route::get('/products',                 'ProductController@index');

Route::group(['prefix' =>     'admin'], function() {
    Route::apiResources([
//        'banners'    => BannerController::class,
//        'categories' => CategoryController::class,
//        'products'   => ProductController::class,
//        'options'    => OptionController::class,
//        'values'     => OptionValueController::class,
    ]);
    // /*BANNER
    Route::get('/banners',                  'BannerController@index');
    Route::post('/banners',                 'BannerController@store');
    Route::get('/banners/{id}',             'BannerController@show');
    Route::post('/banners/{id}/edit',       'BannerController@update');
    Route::delete('/banners/{id}',          'BannerController@destroy');
    // CATEGORY
    Route::get('/categories',               'CategoryController@index');
    Route::post('/categories',              'CategoryController@store');
    Route::get('/categories/{id}',          'CategoryController@show');
    Route::post('/categories/{id}/edit',    'CategoryController@update');
    Route::delete('/categories/{id}',       'CategoryController@destroy');

    // PRODUCTS
//    Route::get('/products',                 'ProductController@index');
//    Route::post('/products',                'ProductController@store');
//    Route::get('/products/{id}',            'ProductController@show');
//    Route::put('/products/{id}',            'ProductController@update');
//    Route::delete('/products/{id}',         'ProductController@destroy');

    // OPTIONS
    Route::get('/options',                 'OptionController@index');
    Route::post('/options',                'OptionValueController@store');
    Route::get('/options/{id}',            'OptionController@show');
    Route::post('/options/{id}/edit',      'OptionController@update');
    Route::delete('/options/{id}',         'OptionController@destroy');
});
