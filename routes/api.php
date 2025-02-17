<?php

use App\Http\Controllers\V1\BalanceWithdrawalController;
use App\Http\Controllers\V1\AccountController;
use App\Http\Controllers\V1\CourtController;
use App\Http\Controllers\V1\InvoiceController;
use App\Http\Controllers\V1\PaymentWebhookController;
use App\Http\Controllers\V1\RatingController;
use App\Http\Controllers\V1\ScheduleController;
use App\Http\Controllers\V1\TransactionController;
use App\Http\Controllers\V1\VenueController;
use App\Mail\SendOtpCode;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::get('/test', function() { //simple test to call laravel API
    return "API Success!";
});

Route::get('/testEmail', function() {
    Mail::to("christianwillson1211@gmail.com")->send(new SendOtpCode("1234"));
    return true;
});

Route::group(['prefix' => 'v1'], function() {

    Route::group(['prefix' => 'account'], function() {
        Route::post('/generateOtpCode', [AccountController::class, "handleOtpCode"]);
        // Route::post('/activate', [AccountController::class, "activateAccount"]);
        
        Route::post('/verifyCode', [AccountController::class, "verifyCode"]);
        Route::post('/changePassword', [AccountController::class, "changePassword"]);
    });

    Route::group(['middleware' => 'auth:sanctum'], function() {

        Route::get('/venueFilterOptions', [VenueController::class, "filterOptions"]);
        Route::get('/venueSearchSuggestions', [VenueController::class, "searchSuggestion"]);
        Route::get('/transactionFilterOptions', [TransactionController::class, "filterOptions"]);

        Route::group(['prefix' => 'venues'], function() {
            Route::post('/', [VenueController::class, "store"]);
            
            Route::post('/{venue:id}', [VenueController::class, "update"]);
            Route::patch('/{venue:id}', [VenueController::class, "update"]);
            
            Route::delete('/{venue:id}', [VenueController::class, "destroy"]);
        });

        Route::group(['prefix' => 'courts'], function() {
            Route::get('/types', [CourtController::class, "getCourtTypes"]);
            Route::post('/{court:id}/setCloseDay', [CourtController::class, "courtCloseDay"]);

            Route::post('/', [CourtController::class, "store"]);
            
            Route::post('/{court:id}', [CourtController::class, "update"]);
            Route::patch('/{court:id}', [CourtController::class, "update"]);
            
            Route::delete('/{court:id}', [CourtController::class, "destroy"]);
        });

        Route::group(['prefix' => 'schedules'], function() {
            Route::get('/', [ScheduleController::class, "index"]);

            Route::post('/generate', [ScheduleController::class, "generateSchedules"]);

            Route::post('/', [ScheduleController::class, "store"]);
            
            Route::delete('/{schedule:id}', [ScheduleController::class, "destroy"]);
        });

        Route::group(['prefix' => 'transactions'], function() {
            Route::get('/', [TransactionController::class, "index"]);
            Route::get('/{transaction:external_id}', [TransactionController::class, "show"]);
            
            Route::post('/checkSchedules', [TransactionController::class, "checkSchedules"]); // untuk cek / konfirmasi jadwal sebelum setuju memesan
            Route::post('/', [TransactionController::class, "store"]);
            
            Route::post('/{transaction:external_id}/cancelConfirmation', [TransactionController::class, "checkTransactionCancelation"]); // untuk cek / konfirmasi bisa melakukan pembatalan / tidak beserta alsannya
            Route::post('/{transaction:external_id}/cancel', [TransactionController::class, "cancelTransaction"]);
            
            Route::patch('/{transaction:external_id}', [TransactionController::class, "update"]);
        });

        Route::group(['prefix' => 'ratings'], function() {
            Route::get('/', [RatingController::class, "index"]);
            Route::post('/', [RatingController::class, "store"]);
        });

        Route::group(['prefix' => 'account'], function() {
            Route::get('/{user:id}', [AccountController::class, "show"]);
            Route::post('/{user:id}', [AccountController::class, "updateData"]);
        });

        Route::get('/users/{ownerId}/employees', [AccountController::class, "getEmployees"]);

        Route::get('/balanceDetails', [BalanceWithdrawalController::class, 'index']);

    });

    Route::group(['prefix' => 'venues'], function() {
        Route::get('/', [VenueController::class, "index"]);
        Route::get('/{venue:id}', [VenueController::class, "show"]);

    });

    Route::group(['prefix' => 'courts'], function() {
        Route::get('/', [CourtController::class, "index"]);
        Route::get('/{court:id}', [CourtController::class, "show"]);
    });

    Route::post('/register', [AccountController::class, "register"]);
    Route::post('/login', [AccountController::class, "handleLogin"]);

    Route::post('/generateGuestToken', [AccountController::class, "generateGuestToken"]);
});

Route::post('/payments/webhook/xendit', [PaymentWebhookController::class, "xenditWebhook"]);
Route::get('/invoices/{invoiceId}', [InvoiceController::class, "getXenditInvoice"]);

// Auth::routes(['verify' => true]); //???