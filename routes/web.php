<?php

use App\Events\MyEvent;
use App\Http\Controllers\InvoicesController;
use App\Jobs\ProcessPodcast;
use App\Mail\TestQueuedMail;
use App\Services\OtpMailService;
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
Route::match(['get','post'], 'auth/{provider}/callback', [SocialController::class, 'handleProviderCallback']);

Route::get('job', function (){
    dispatch(new ProcessPodcast());
});

Route::get('event', function (){
    event(new MyEvent('hello world'));
});

Route::get('cancel-pending-reservations', function (){
    Artisan::call('app:cancel-pending-reservations');
    die(); // to not log
})->middleware(['throttle:30,1']);

Route::get('queue-work', function (){
    Artisan::call('queue:work');
    sleep(25);
    die();// to not log
})->middleware(['throttle:30,1']);

Route::get('invoices/{referenceId}',[InvoicesController::class , 'showInvoice'])->name('invoice.show');
Route::get('invoices/{referenceId}/pdf',[InvoicesController::class , 'download'])->name('invoice.download');
Route::get('ar-pdf',[InvoicesController::class , 'arPdf']);

Route::get('send-sms', function(\App\Services\SmsService $twilio)
{
    $otp = rand(1000, 9999);
//    $twilio->sendByTwilio('+96565708188', $otp);
    return response()->json([
        'message' => 'OTP sent successfully'
    ]);
});

Route::get('send-email', function (){
//    app(OtpMailService::class)->send(
//        "eng.mg2011@gmail.com",
//        '12345'
//    );
//    Mail::to('eng.mg2011@gmail.com')
//        ->queue(new TestQueuedMail('Hello 👋 this email is queued'));
//    return 'Mail queued';

//    $email = new Mail;
//    $email->setFrom("test@example.com", "Example User");
//    $email->setSubject("Sending with SendGrid is Fun");
//    $email->addTo("test@example.com", "Example User");
//    $email->addContent("text/plain", "and easy to do anywhere, even with PHP");
//    $email->addContent(
//        "text/html", "<strong>and easy to do anywhere, even with PHP</strong>"
//    );
//    $sendgrid = new \SendGrid(getenv('SENDGRID_API_KEY'));
//// $sendgrid->setDataResidency("eu");
//// uncomment the above line if you are sending mail using a regional EU subuser
//    try {
//        $response = $sendgrid->send($email);
//        print $response->statusCode() . "\n";
//        print_r($response->headers());
//        print $response->body() . "\n";
//    } catch (Exception $e) {
//        echo 'Caught exception: '. $e->getMessage() ."\n";
//    }
});


