<?php

use Illuminate\Support\Facades\Route;

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

Route::prefix('/history')->name('history.')->group(function () {

    Route::resource('list-claim', 'History\CreateClaimController', ['only' => ['index','create']]);
    Route::resource('list-treat', 'History\TreatClaimController', ['only' => ['index']]);

});
