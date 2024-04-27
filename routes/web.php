<?php

use App\Http\Controllers\V1\ImageController;
use App\Http\Controllers\V1\ScheduleController;
use App\Http\Controllers\VerifyEmailController;
use GuzzleHttp\Psr7\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

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

// Route::get('/', function () {
//     return view('swagger-ui');
// });

Route::get('/', function () {
    return "KitaGerak API";
});

Route::get('/', function () {
    return view('layouts.light');
});

Route::get('/verified', function () {
    return "Berhasil verifikasi email! Silahkan kembali ke aplikasi KitaGerak...";
});


Route::get('/test', [ScheduleController::class, "generateSchedule"]);
Route::get('/images/{fileName}', [ImageController::class, "show"]);

Route::get('/email/verify/{id}/{hash}', [VerifyEmailController::class, '__invoke']);

Auth::routes(['verify' => true]);
Route::get('/images/{folder}/{fileName?}', [ImageController::class, "show"]);

Route::get('/payment-success', function() {
    //TODO:: Create an UI
    return "Pembayaran Berhasil";
});

Route::get('/payment-failed', function() {
    //TODO:: Create an UI
    return "Pembayaran Gagal";
});