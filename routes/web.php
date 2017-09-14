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


Route::get('/', function (){
    //echo 'Ajala Bot';
});
Route::get('/policy', 'MenuController@policy');

//Route::get('/bot', 'BotController@bot')->middleware('verifybot');

Route::match(['post', 'get'], '/bot', 'BotController@bot')->middleware('verifybot');
//Auth::routes();

Route::get('/home', 'HomeController@index')->name('home');

Route::get('sendemail', function () {
    //return view('emails.welcome');

    $data = array(
        'name' => "Learning Laravel",
    );

    $mail = Mail::send('emails.welcome', $data, function ($message) {

        $message->from('yourEmail@domain.com', 'Learning Laravel');
        $message->cc('radioactive.uche11@gmail.com')->subject('Learning Laravel test email');
        $message->to('naijacompetitions@gmail.com')->subject('Learning Laravel test email');

    });
    if($mail){
        return 'true';
    }else{
        return error_get_last()['message'];
    }
    //return "Your email has been sent successfully";


});
