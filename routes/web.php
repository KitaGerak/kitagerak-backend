<?php

// use App\Http\Controllers\V1\ScheduleController;

use App\Http\Controllers\AccountController;
use App\Http\Controllers\CourtController;
use App\Http\Controllers\CourtTypeController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\VenueController;
// use App\Http\Controllers\VerifyEmailController;
// use Illuminate\Foundation\Auth\EmailVerificationRequest;
// use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get("/images/blank", function() {
    $folder_name = "private/images";

    $path = $folder_name.'/'."blank.webp";

    if(!Storage::exists($path)){
        abort(404);
    }

    return Storage::response($path);
});

Route::get("/images/{sector}/{id}/{fileName}", function($sector, $id, $fileName) {
    $folder_name = "private/images/$sector/$id";

    $path = $folder_name.'/'.$fileName;

    if(!Storage::exists($path)){
        abort(404);
    }

    return Storage::response($path);
});

Route::get('/', function () {
    return view('layouts.light');
});

Route::get('/verified', function () {
    return "Berhasil verifikasi email! Silahkan kembali ke aplikasi KitaGerak...";
});


// Route::get('/test', [ScheduleController::class, "generateSchedule"]);
// Route::get('/images/{fileName}', [ImageController::class, "show"]);

// Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke']);

// Auth::routes(['verify' => true]);
// Route::get('/images/{folder}/{fileName?}', [ImageController::class, "show"]);

Route::get('/payment-success', function() {
    //TODO:: Create an UI
    return "Pembayaran Berhasil";
});

Route::get('/payment-failed', function() {
    //TODO:: Create an UI
    return "Pembayaran Gagal";
});

Route::group(['middleware' => 'auth'], function() {
    Route::group(['prefix' => 'venues'], function(){
        Route::get('/', [VenueController::class, 'index']);
        Route::get('/{venue:id}', [VenueController::class, 'detail']);
        Route::post('/{venue:id}/accept', [VenueController::class, 'acceptVenueRegistration']);
        Route::post('/{venue:id}/decline', [VenueController::class, 'declineVenueRegistration']);
    });
    
    Route::group(['prefix' => 'courts'], function(){
        Route::get('/', [CourtController::class, 'index']);
        Route::get('/{court:id}', [CourtController::class, 'show']);
        Route::post('/{court:id}/accept', [CourtController::class, 'acceptCourtRegistration']);
        Route::post('/{court:id}/decline', [CourtController::class, 'declineCourtRegistration']);
    });

    Route::group(['prefix' => 'courtTypes'], function(){
        Route::get('/', [CourtTypeController::class, 'index']);
        Route::post('/', [CourtTypeController::class, 'store']);
        
        Route::post('{courtType:id}/update', [CourtTypeController::class, 'update']);
        Route::post('{courtType:id}/deactivate', [CourtTypeController::class, 'destroy']);
        Route::post('{courtType:id}/reactivate', [CourtTypeController::class, 'reactivate']);
    });

    Route::get('/home', [HomeController::class, "index"]);
    Route::post('/systemWarnings/{systemWarning:id}', [HomeController::class, "removeSystemWarning"]);
});

Route::get('/halamanrahasiaregisterhanyauntukadmin', [AccountController::class, "register"]);
Route::get('/halamanrahasiaregisterhanyauntukadmin2', [AccountController::class, "register2"]);
Route::get('/halamanrahasialoginhanyauntukadmin', [AccountController::class, "login"])->name('login')->middleware('guest');

Route::post('/register', [AccountController::class, "registerAddData"]);
Route::post('/login', [AccountController::class, "authenticate"]);

Route::post('/logout', [AccountController::class, "logout"])->middleware('auth');