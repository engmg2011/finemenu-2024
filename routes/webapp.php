<?php


// TODO :: put admin only roles
use App\Constants\RolesConstants;
use App\Http\Controllers\WebAppController;

// Routes for api/webapp
Route::group(['prefix' => 'webapp',
    'middleware' => ['recaptcha']
], function () {


    Route::get('menus/{id}', [WebAppController::class, 'nestedMenu']);
    Route::get('diet-restaurant/{id}', [WebAppController::class, 'dietRestaurant']);

    Route::get('branches/{slug}', [WebAppController::class, 'branchMenu']);

});
