<?php

use Illuminate\Support\Facades\Auth;

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

// Route::get('/', function () {
//     return view('welcome');
// });

Route::resource('/','Home\QuestionsController');


// Route::group(['domain' => 'admin.zt.com'], function() {

	Auth::routes();

	Route::get('/home', 'HomeController@index');
	// 验证邮件 token
	Route::get('email/verify/{token}', ['as'=>'email.verify', 'uses'=>'EmailController@verify']);
	Route::resource('questions', 'Home\QuestionsController', ['names'=>[
		'create' => 'question.create',
		'show' => 'question.show',
	]]);

	Route::post('questions/{question}/answer', 'Home\AnswersController@store');

// });

