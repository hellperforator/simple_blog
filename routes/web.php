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

Route::get('/', 'Post@index');

Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('/post', 'Post@index');

Route::get('/post/top/', 'Post@top');

Route::post('/post/save', 'Post@save');

Route::post('/post/score', 'Post@score');

Route::post('/post/comment', 'Post@comment');

Route::get('/post/view/{id}', 'Post@view');
