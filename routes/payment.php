<?php

use App\Http\Controllers\PaymentController;

Route::group(['prefix' => 'payment', 'as' => 'payment.'], function () {
    Route::get('success', [PaymentController::class, 'success'])->name('success');
    Route::get('failed', [PaymentController::class, 'failed'])->name('failed');
    Route::get('hesabe/checkout/{referenceId}', [PaymentController::class, 'hesabeCheckout'])->name('hesabe-checkout');
    Route::get('hesabe/completed/{referenceId}', [PaymentController::class, 'hesabeCompleted'])->name('hesabe-completed');
    Route::post('hesabe/completed/{referenceId}', function (){
        //[PaymentController::class, 'hesabeCompleted']
        // may be coming from webhook at hesabe
        return response()->json("completed payment");
    })->name('hesabe-completed');
});
