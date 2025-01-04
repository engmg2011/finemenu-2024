<?php

use App\Http\Controllers\PaymentController;

Route::group(['prefix' => 'payment', 'as' => 'payment.'], function () {
    Route::get('/success/{referenceId}', [PaymentController::class, 'hesabeCompleted'])->name('success');
    Route::get('/failure/{referenceId}', [PaymentController::class, 'failed'])->name('failure');
    Route::get('hesabe/checkout/{referenceId}', [PaymentController::class, 'hesabeCheckout'])->name('hesabe-checkout');
    Route::get('hesabe/completed/{referenceId}', [PaymentController::class, 'hesabeCompleted'])->name('hesabe-completed');
});
