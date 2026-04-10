<?php

use App\Http\Controllers\SquareController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\AuthorizeController;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;

if(App::isProduction()) {
    Route::get('/login', fn() => redirect()->route('/'));
}

// stripe
Route::controller(SquareController::class)
    ->prefix('sq')
    ->group(function() {
    Route::get('/return/{payment}', 'return');
});

// authorize
Route::controller(AuthorizeController::class)
    ->prefix('authorize')
    ->group(function() {
    Route::get('/form/{payment}', 'form');
});

Route::get('s/success/{payment}', [StripeController::class, 'success']);
Route::get('/', [UserController::class, 'login'])->name('login');
Route::get('customer/login', fn() => view('application')->with(['title' => 'Visas Canada']));

Route::get('logout/{type?}', [UserController::class, 'logout']);

Route::post('/user-login', [UserController::class, 'apiLogin']);

Auth::routes();

Route::get('retainer/{uuid}', fn() => view('application')->with(['title' => 'Visas Canada']));

Route::get('/home', fn() => view('application'))->where('any', '.*')->middleware(['auth', 'auth.agent']);
Route::get('/customer/{any?}', fn() => view('application')->with(['title' => 'Visas Canada']))->where('any', '.*')->middleware('auth');
Route::get('{any?}', fn() => view('application'))->where('any', '.*')->middleware('auth');