<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AgentBreakController;
use App\Http\Controllers\AgentController;
use App\Http\Controllers\AuthorizeController;
use App\Http\Controllers\ChargebacksController;
use App\Http\Controllers\ConversionRateController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RetainerController;
use App\Http\Controllers\SquareController;
use App\Http\Controllers\StripeController;
use App\Http\Controllers\UserController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// sleep(1);

// stripe
Route::controller(StripeController::class)->prefix('s')->group(function() {
    Route::post('/return', 'return');
});

// square
Route::controller(SquareController::class)->prefix('sq')->group(function() {
    Route::post('/webhook', 'webhook');
});

// authorize
Route::controller(AuthorizeController::class)
    ->prefix('authorize')
    ->group(function() {
    Route::post('/webhook/{payment}', 'webhook');
});

Route::get('tv-data', [AdminController::class, 'tvData']);
Route::get('tv-last-payment/{id}', [AdminController::class, 'lastPayment']);

// products
Route::controller(LeadController::class)->prefix('leads')->group(function() {
    Route::get('/callbacks/events/{user?}', 'events');
    Route::post('/filter-section', 'filterSection');
    Route::post('/back-office', 'backOffice');
    Route::post('{lead}/back-office/doc/{slug}', 'uploadBackOfficeDoc');
    Route::post('{lead}/spouse', 'spouse');
    Route::get('/{lead}/back-office/{slug}/{status}', 'updateBackOfficeDoc');
    Route::get('/payments/{latest?}', 'allPayments');
    Route::get('/prev/{lead}/{type}', 'prev');
    Route::get('/next/{lead}/{type}', 'next');
    Route::get('/{lead}/latest/{last?}', 'latestPayments');
    Route::post('/delete', 'destroy');
    Route::post('/put-back', 'putBack');
    Route::post('/move', 'moveLeads');
    Route::post('/{lead}/comments', 'saveComments');
    Route::get('/{lead}/recall', 'recall');
    Route::get('/{lead}/{isAdmin?}', 'show');
    Route::post('{lead}/payment-link', 'paymentLink');
    Route::post('/{type}', 'index');

    Route::post('/callbacks/{lead}', 'addCallback');

    Route::post('files/{lead}', 'saveFile');
    Route::delete('files/{lead}', 'deleteFile');

    Route::put('{lead}/{field}', 'updateField');

    Route::post('/delete', 'destroy');
    Route::post('/', 'create');

    Route::post('/set-status/{lead}/{status}', 'setReason');
})->middleware('auth:sanctum');

// retainers
Route::controller(RetainerController::class)->prefix('retainers')->group(function() {
    Route::post('{lead}/send', 'send');
    Route::post('{lead}/results', 'results');
    Route::post('{lead}/results/delete', 'deleteResult');
    Route::get('{lead}/visa/{id}', 'updateVisa');
})->middleware('auth:sanctum');

Route::controller(RetainerController::class)->prefix('retainers')->group(function() {
    Route::get('{uuid}', 'show');
    Route::post('{uuid}/sign', 'sign');
});

// admin
Route::controller(AdminController::class)->prefix('admin')->group(function() {
    Route::get('customers/setup', 'setUp');
    Route::get('customers/{lead}', 'customer');
    Route::get('customers/creds/{lead}/view', 'getCreds');
    Route::post('customers/creds/{lead}/send', 'sendCreds');
    Route::post('customers/{lead}', 'customerUpdate');
    Route::post('/leads/closed', 'closed');
    Route::post('/leads/assigned', 'assigned');
    Route::post('/leads/disqualified/{type}', 'disqualified');
    Route::post('/leads/{type}/{first?}', 'leads');
    Route::post('/csv/headers', 'getCSVHeaders');
    Route::post('/csv/{upload}', 'uploadCsv');
    Route::get('/csv/{upload}', 'csvErrors');
    
    Route::get('/agents', 'agents');
    Route::put('/agents/reset-password/{user}', 'resetPassword');
    Route::delete('/agents', 'rmvAgents');
    Route::post('/agents/{agent}/{ignore?}', 'assignAgents');
})->middleware('auth:sanctum');

// admin
Route::controller(AgentController::class)->prefix('agents')->group(function() {
    Route::post('/', 'index');
    Route::get('/commission', 'commission');
    Route::get('/reset-password', 'resetPassword');
    Route::post('/reset-password', 'updatePassword');
    Route::get('/{user}', 'show');
    Route::delete('/{user}', 'destroy');
    Route::post('/{user}', 'update');
    Route::post('goal/{user}', 'updateGoal');
    Route::put('/', 'store');
})->middleware('auth:sanctum');

Route::controller(PaymentController::class)->prefix('payments')->group(function() {
    Route::post('/opportunities', 'opportunities');
    Route::post('/type/{type}', 'index');
    Route::post('/{user}', 'agentPayments');
})->middleware('auth:sanctum');

// breaks
Route::controller(AgentBreakController::class)->prefix('breaks')->group(function() {
    Route::post('/', 'index');
    Route::get('/available', 'available');
    Route::get('/start/{type}', 'start');
    Route::get('/stop', 'stop');
})->middleware('auth:sanctum');

// users
Route::controller(UserController::class)->prefix('users')->group(function() {
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/verify', 'verify');
        Route::post('/', 'store');
    });

    Route::post('/login', 'apiLogin');
});


// customers
Route::controller(CustomerController::class)->middleware('auth:sanctum')->prefix('customers')->group(function() {
    Route::get('/customer', 'show');
    Route::post('/doc/{slug}', 'doc');
});

// chargebacks
Route::controller(ChargebacksController::class)
    ->middleware('auth:sanctum')
    ->prefix('chargebacks')
    ->group(function() {
    Route::get('/', 'index');
    Route::get('/user', 'userChargebacks');
    Route::post('/', 'store');
});

// rates
Route::middleware('auth:sanctum')->controller(ConversionRateController::class)->prefix('rates')->group(function() {
    Route::get('/', 'show');
    Route::post('/{conversionRate}', 'update');
});

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// External API
Route::middleware('auth.basic')->post('/v1/leads', [LeadController::class, 'create']);