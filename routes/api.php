<?php

use App\Http\Controllers\V1\AccountController;
use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\CourtController;
use App\Http\Controllers\V1\CourtTypeController;
use App\Http\Controllers\V1\InvoiceController;
use App\Http\Controllers\V1\PaymentWebhookController;
use App\Http\Controllers\V1\RatingController;
use App\Http\Controllers\V1\ScheduleController;
use App\Http\Controllers\V1\TransactionController;
use App\Http\Controllers\V1\VenueController;
use App\Http\Controllers\V1\VenueOwnerController;
use Illuminate\Support\Facades\Auth;
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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });


Route::group(['prefix' => 'v1'], function() {

    Route::group(['prefix' => 'venues'], function() {
        Route::get('/', [VenueController::class, "index"]);
        Route::get('/{venue:id}', [VenueController::class, "show"]);
        Route::post('/', [VenueController::class, "store"]);

    });

    Route::group(['prefix' => 'courts'], function() {
        Route::get('/', [CourtController::class, "index"]);
        Route::get('/{court:id}', [CourtController::class, "show"]);
    });

    Route::group(['middleware' => 'auth:sanctum'], function() {

        Route::get('/filterOptions', [VenueController::class, "filterOptions"]);
        Route::get('/venueSearchSuggestions', [VenueController::class, "searchSuggestion"]);
        Route::get('/transactionFilterOptions', [TransactionController::class, "filterOptions"]);

        Route::group(['prefix' => 'venues'], function() {
            Route::post('/', [VenueController::class, "store"]);
            Route::put('/{venue:id}', [VenueController::class, "update"]);
            Route::patch('/{venue:id}', [VenueController::class, "update"]);
            Route::post('/bulk', [VenueController::class, "bulkStore"]);
            Route::delete('/{venue:id}', [VenueController::class, "destroy"]);
        });

        Route::group(['prefix' => 'courts'], function() {
            Route::post('/', [CourtController::class, "store"]);
            Route::post('/{court:id}/updateImages', [CourtController::class, "updateImages"]);
            Route::put('/{court:id}', [CourtController::class, "update"]);
            Route::patch('/{court:id}', [CourtController::class, "update"]);
            Route::delete('/{court:id}', [CourtController::class, "destroy"]);
        });

        Route::group(['prefix' => 'schedules'], function() {
            //MOVED
            Route::get('/', [ScheduleController::class, "index"]);
            //END-MOVED
            Route::post('/', [ScheduleController::class, "store"]);
            Route::post('/bulkStore', [ScheduleController::class, "bulkStore"]);
            Route::put('/{schedule:id}', [ScheduleController::class, "update"]);
            Route::patch('/{schedule:id}', [ScheduleController::class, "update"]);
            Route::delete('/{schedule:id}', [ScheduleController::class, "destroy"]);
            Route::delete('/', [ScheduleController::class, "destroyMultiple"]);
        });

        Route::group(['prefix' => 'transactions'], function() {
            Route::get('/', [TransactionController::class, "index"]);
            Route::get('/{transaction:external_id}', [TransactionController::class, "show"]);
            Route::post('/', [TransactionController::class, "store"]);
            Route::post('/bulkStore', [TransactionController::class, "bulkStore"]);
            Route::post('/{transaction:external_id}/cancel', [TransactionController::class, "cancelSchedule"]);
            // Route::patch('/{transaction:external_id}', [TransactionController::class, "update"]);
        });

        Route::group(['prefix' => 'ratings'], function() {
            Route::get('/', [RatingController::class, "index"]);
            Route::post('/', [RatingController::class, "store"]);
            Route::post('/{rating:id}', [RatingController::class, "storePhoto"]);
        });

        Route::group(['prefix' => 'account'], function() {
            Route::get('/{user:id}', [AccountController::class, "show"]);
            Route::post('/updateData/{user:id}', [AccountController::class, "updateData"]);
        });
        
        Route::group(['prefix' => 'admin'], function() {
            Route::group(['prefix' => 'court-types'], function() {
                Route::get('/', [CourtTypeController::class, "index"]);
                Route::post('/', [CourtTypeController::class, "store"]);
                Route::put('/{courtType:id}', [CourtTypeController::class, "update"]);
                Route::delete('/{courtType:id}', [CourtTypeController::class, "delete"]);
            });
        });

        Route::group(['prefix' => 'venue-owner'], function() {
            Route::get('/get-employees/{ownerId}', [VenueOwnerController::class, "getEmployees"]);
        });

    });

    Route::post('/register', [AuthController::class, "register"]);
    Route::post('/login', [AuthController::class, "login"]);
});

Route::post('/payments/webhook/xendit', [PaymentWebhookController::class, "xenditWebhook"]);
Route::get('/invoices/{invoiceId}', [InvoiceController::class, "getXenditInvoice"]);

Auth::routes(['verify' => true]);