<?php

use App\Events\MyEvent;
use App\Http\Controllers\InvoicesController;
use App\Http\Controllers\WebAppController;
use App\Jobs\ProcessPodcast;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialController;


//use App\Events\SendOrders;


//\Auth::routes();
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

Route::get('/send', [HomeController::class, 'send'])->name('home.send');*/

Route::get('orders-sender', function () {
    $business_id = request()->get('businessId');
    event(new \App\Events\NewOrder(78));
//    event(new \App\Events\SendOrders($business_id));
//    event(new MyEvent('hello world'));

});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    })->name('dashboard');
});

Route::get('auth/app-token', [SocialController::class, 'appToken']);
Route::get('auth/{provider}', [SocialController::class, 'redirectToProvider']);
Route::get('auth/{provider}/callback', [SocialController::class, 'handleProviderCallback']);

Route::get('job', function (){
    dispatch(new ProcessPodcast());
});

Route::get('event', function (){
    event(new MyEvent('hello world'));
});

Route::get('cancel-pending-reservations', function (){
    Artisan::call('app:cancel-pending-reservations');
    die(); // to not log
})->middleware(['throttle:10,5']);

Route::get('queue-work', function (){
    Artisan::call('queue:work');
    sleep(25);
    die();// to not log
})->middleware(['throttle:10,5']);

Route::get('invoices/{id}',[InvoicesController::class , 'showInvoice'])->name('invoice.show');
Route::get('invoices/{id}/pdf',[InvoicesController::class , 'download'])->name('invoice.download');


Route::get('/send-test-mail', [WebAppController::class, 'testMail'] );
