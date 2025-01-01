<?php

use App\Http\Controllers\PaymentController;

Route::group(['prefix' => 'payment', 'as' => 'payment.', 'middleware' => ['auth:sanctum']], function () {
    Route::get('/success/{referenceId}', [PaymentController::class, 'success'])->name('success');
    Route::get('/failure/{referenceId}', [PaymentController::class, 'failure'])->name('failure');
});

Route::group(['prefix' => 'payment', 'as' => 'payment.'], function () {
    Route::get('hesabe/checkout/{referenceId}', [PaymentController::class, 'hesabeCheckout'])->name('hesabe-checkout');
    Route::get('hesabe/completed/{referenceId}', [PaymentController::class, 'hesabeCompleted'])->name('hesabe-completed');
});
