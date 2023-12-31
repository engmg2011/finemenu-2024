<?php

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

Route::get('/', function () {
    return view('welcome');
    //return view('index');
});

Route::get('/phpinfo', fn() => phpinfo());
/*
Route::get('notification-sender', function (){
    $keyword = request('message');
    event(new BroadcastingMessageToUser($keyword));
});

Route::get('notification', function (){
    return view('notification');
});

Route::get('orders-sender', function (){
    $restaurant_id = request()->get('restaurantId') ;
    event(new SendOrders($restaurant_id));
});

Route::get('/generate-qrcode', [QrCodeController::class, 'index']);
Route::get('/save-qrcode', [QrCodeController::class, 'save']);

Route::get('/send', [HomeController::class, 'send'])->name('home.send');*/
