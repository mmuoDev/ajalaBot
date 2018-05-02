<?php
use Illuminate\Support\Facades\DB;

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


Route::get('/', function (){
    $string = "ajala33";
    dd(str_replace('ajala', '', $string));
    
});

Auth::routes();
Route::get('logout', 'Auth\LoginController@logout');

Route::get('/policy', 'MenuController@policy');

//Route::get('/bot', 'BotController@bot')->middleware('verifybot');

Route::match(['post', 'get'], '/bot', 'BotController@bot')->middleware('verifybot');

Route::get('/home', 'HomeController@index')->name('home');

Route::group(['prefix' => 'travels'], function (){
    Route::match(['post', 'get'], '/', 'TravelController@index')->name('travels');
    Route::match(['post', 'get'], '/create', 'TravelController@create');
    Route::match(['post', 'get'], '/edit/{id}', 'TravelController@update')->name('edit_travels');
    Route::get('/download-file/{id}', 'TravelController@download_file');
});


