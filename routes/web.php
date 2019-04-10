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

use App\Http\Middleware\ApiAuthMiddleware;

Route::get('/', function () {
    return view('welcome');
});

Route::get("/prueba/{nombre?}", function ($nombre = null) {
    $texto = '<h1>texto en ruta</h1>';
    $texto .= 'Nombre: '.$nombre;

    return view('prueba', array(
        'texto' => $texto
    ));
});



/* Métodos de prueba de la API */
Route::get('/animales', 'PruebaController@index');
Route::get('/test-orm', 'PruebaController@testOrm');

Route::get('/user/test', 'UserController@test');
Route::get('/category/test', 'CategoryController@test');
Route::get('/post/test', 'PostController@test');

//Usuarios
Route::post('api/register', 'UserController@register');
Route::post('api/login', 'UserController@login');
Route::put('api/user/update', 'UserController@update');
Route::post('api/user/upload', 'UserController@upload')->middleware(ApiAuthMiddleware::class);
Route::get('api/user/avatar/{filename}', 'UserController@getImage');
Route::get('api/user/detail/{id}', 'UserController@detail');

//Categorias
Route::resource('api/category', 'CategoryController');

//posts
Route::resource('api/post', 'PostController');
Route::post('api/post/upload', 'PostController@upload');
Route::get('api/post/image/{filename}', 'PostController@getImage');
Route::get('api/post/user/{id}', 'PostController@getPostByUser');
Route::get('api/post/category/{id}', 'PostController@getPostByCategory');