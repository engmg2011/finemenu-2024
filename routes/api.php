<?php

use App\Constants\ConfigurationConstants;
use App\Constants\RolesConstants;
use App\Http\Controllers\AddonsController;
use App\Http\Controllers\CategoriesController;
use App\Http\Controllers\ContentsController;
use App\Http\Controllers\DevicesController;
use App\Http\Controllers\DietPlansController;
use App\Http\Controllers\DietPlanSubscriptionsController;
use App\Http\Controllers\DiscountsController;
use App\Http\Controllers\EventsController;
use App\Http\Controllers\FeaturesController;
use App\Http\Controllers\LocalesController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\MenusController;
use App\Http\Controllers\PackagesController;
use App\Http\Controllers\PricesController;
use App\Http\Controllers\PusherAuthController;
use App\Http\Controllers\ServicesController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\SubscriptionsController;
use App\Http\Controllers\WebAppController;
use App\Http\Middleware\SetRequestModel;
use Berkayk\OneSignal\OneSignalClient;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------:------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Don't move down
Route::group(['middleware' => ['auth:sanctum', 'throttle:300,1']], function () {
    Route::post('/pusher/auth', [PusherAuthController::class, 'authenticate']);
    Route::group(['prefix' => 'media'], function () {
        Route::post('upload', [MediaController::class, 'postUpload']);
    });
});

// Features for guests
Route::group(['prefix' => 'features'], function () {
    Route::get('/', [FeaturesController::class, 'index']);
    Route::group(['prefix' => '/categories'], function () {
        Route::get('/', [CategoriesController::class, 'featuresCategories']);
        Route::group(['prefix' => '{modelId}'], function () {
            Route::get('/', [CategoriesController::class, 'show']);
            Route::group(["prefix" => "/settings"], function () {
                Route::get('/', [SettingsController::class, 'listSettings']);
            });
        });
    });
    Route::get('/{id}', [FeaturesController::class, 'show']);
});

// TODO :: put admin only roles
Route::group(['middleware' => ['auth:sanctum', 'throttle:300,1', 'role:' . RolesConstants::ADMIN . '|' . RolesConstants::BUSINESS_OWNER . '|' . RolesConstants::BRANCH_MANAGER]], function () {

    Route::group(['prefix' => 'locales'], function () {
        Route::post("", [LocalesController::class, 'createModel']);
        Route::post("{id}/delete", [LocalesController::class, 'delete']);
    });

    Route::group(['prefix' => 'menu'], function () {
        Route::get("/{id}", [MenusController::class, 'menu']);
    });

    Route::group(['prefix' => 'prices'], function () {
        Route::post('/{id}/delete', [PricesController::class, 'destroy']);
    });

    Route::group(['prefix' => 'services'], function () {
        Route::get('/', [ServicesController::class, 'index']);
        Route::get('/{id}', [ServicesController::class, 'show']);
        Route::post('/', [ServicesController::class, 'create']);
        Route::post('/{id}/delete', [ServicesController::class, 'destroy']);
        Route::post('/{id}', [ServicesController::class, 'update']);
    });

    Route::group(['prefix' => 'events'], function () {
        Route::get('/', [EventsController::class, 'index']);
        Route::get('/{id}', [EventsController::class, 'show']);
        Route::post('/', [EventsController::class, 'create']);
        Route::post('/{id}', [EventsController::class, 'update']);
    });

    Route::group(['prefix' => 'contents'], function () {
        Route::get('/', [ContentsController::class, 'index']);
        Route::get('/{id}', [ContentsController::class, 'show']);
        Route::post('/', [ContentsController::class, 'create']);
        Route::post('/{id}', [ContentsController::class, 'update']);
    });

    Route::group(['prefix' => 'media'], function () {
        Route::get('/', [MediaController::class, 'index']);
        Route::get('/{id}', [MediaController::class, 'show']);
        Route::post('/', [MediaController::class, 'create']);
        Route::post('/{id}', [MediaController::class, 'update']);
        Route::post('/{id}/delete', [MediaController::class, 'destroy']);
    });

    Route::group(['prefix' => 'prices'], function () {
        Route::get('/', [PricesController::class, 'index']);
        Route::get('/{id}', [PricesController::class, 'show']);
        Route::post('/', [PricesController::class, 'create']);
        Route::post('/{id}', [PricesController::class, 'update']);
    });


    Route::group(['prefix' => 'settings'], function () {
        Route::get('/', [SettingsController::class, 'index']);
        Route::get('/{id}', [SettingsController::class, 'show']);
        Route::post('/', [SettingsController::class, 'create']);
        Route::post('/{id}', [SettingsController::class, 'update']);
    });


    Route::group(['prefix' => 'addons'], function () {
        Route::get('/', [AddonsController::class, 'index']);
        Route::get('/{id}', [AddonsController::class, 'show']);
        Route::post('/', [AddonsController::class, 'create']);
        Route::post('/{id}', [AddonsController::class, 'update']);
        Route::post('/{id}/delete', [AddonsController::class, 'destroy']);
    });

    Route::group(['prefix' => 'discounts'], function () {
        Route::get('/', [DiscountsController::class, 'index']);
        Route::get('/{id}', [DiscountsController::class, 'show']);
        Route::post('/', [DiscountsController::class, 'create']);
        Route::post('/{id}', [DiscountsController::class, 'update']);
        Route::post('/{id}/delete', [DiscountsController::class, 'destroy']);
    });


    Route::group(['prefix' => 'packages'], function () {
        Route::get('/', [PackagesController::class, 'index']);
        Route::get('/{id}', [PackagesController::class, 'show']);
        Route::post('/', [PackagesController::class, 'create']);
        Route::post('/{id}/delete', [PackagesController::class, 'destroy']);
        Route::post('/{id}', [PackagesController::class, 'update']);
    });

    Route::group(['prefix' => 'subscriptions'], function () {
        Route::get('/', [SubscriptionsController::class, 'index']);
        Route::get('/{id}', [SubscriptionsController::class, 'show']);
        Route::post('/', [SubscriptionsController::class, 'create']);
        Route::post('/{id}/delete', [SubscriptionsController::class, 'destroy']);
        Route::post('/{id}', [SubscriptionsController::class, 'update']);
    });

    Route::group(['prefix' => 'diet-plan-subscriptions'], function () {
        Route::get('/', [DietPlanSubscriptionsController::class, 'index']);
        Route::get('/{id}', [DietPlanSubscriptionsController::class, 'show']);
        Route::post('/', [DietPlanSubscriptionsController::class, 'create']);
        Route::post('/{id}/delete', [DietPlanSubscriptionsController::class, 'destroy']);
        Route::post('/{id}', [DietPlanSubscriptionsController::class, 'update']);
    });

    Route::group(['prefix' => 'devices'], function () {
        Route::get('/', [DevicesController::class, 'index']);
        Route::get('/{id}', [DevicesController::class, 'show']);
        Route::post('/', [DevicesController::class, 'create']);
        Route::post('/{id}/delete', [DevicesController::class, 'destroy']);
        Route::post('/{id}', [DevicesController::class, 'update']);
    });

    Route::group(['prefix' => 'diet-plans'], function () {
        Route::get('/', [DietPlansController::class, 'index']);
        Route::get('/{id}', [DietPlansController::class, 'show']);
        Route::post('/{id}/subscribe', [DietPlanSubscriptionsController::class, 'subscribe']);
        Route::post('/', [DietPlansController::class, 'create']);
        Route::post('/{id}', [DietPlansController::class, 'update']);
        Route::get('/{id}/delete', [DietPlansController::class, 'destroy']);
    });

});


// TODO :: move admin only routes to here and make admin users
Route::group(['middleware' => ['auth:sanctum', 'throttle:60,1',
    SetRequestModel::class,
    'role:' . RolesConstants::ADMIN . '|' . RolesConstants::BUSINESS_OWNER . '|' . RolesConstants::BRANCH_MANAGER]
], function () {

    Route::group(['prefix' => 'features'], function () {
        Route::group(['prefix' => '/categories'], function () {
            Route::post('/', [CategoriesController::class, 'create']);
            Route::post('/sort', [CategoriesController::class, 'updateSort']);
            Route::group(['prefix' => '{modelId}'], function () {
                Route::post('/', [CategoriesController::class, 'updateFeatureCategory']);
                Route::post('/delete', [CategoriesController::class, 'destroyFeatureCategory']);
                Route::group(["prefix" => "/settings"], function () {
                    Route::post('/set', [SettingsController::class, 'setSetting']);
                    Route::get('/{settingId}/delete', [SettingsController::class, 'deleteSetting']);
                });
            });
        });
        Route::post('/', [FeaturesController::class, 'create']);
        Route::post('/sort', [FeaturesController::class, 'sort']);
        Route::post('/{id}', [FeaturesController::class, 'update']);
        Route::get('/{id}', [FeaturesController::class, 'show']);
        Route::post('/{id}/delete', [FeaturesController::class, 'destroy']);
    });

});


Route::get('qr-app-version', [WebAppController::class, 'QRAppVersion']);
Route::get('tablet-app-version', [WebAppController::class, 'TabletAppVersion']);
Route::get('orders-app-version', [WebAppController::class, 'OrdersAppVersion']);

Route::get('business-types', [WebAppController::class, 'businessTypes']);

Route::get('send', [WebAppController::class, 'send']);


Route::get('sendOS', function () {
    $playerIds = [
        '41da9a85-51d1-4463-bb1f-d8190bf8ada2'
    ];
    $business = \App\Models\Business::find(4);
    $oneSignal = new OneSignalClient(
        $business->getConfig(ConfigurationConstants::QR_ONESIGNAL_APP_ID),
        $business->getConfig(ConfigurationConstants::QR_ONESIGNAL_REST_API_KEY),
        ''
    );
    $oneSignal->sendNotificationToUser(
        "Your notification message a2",
        $playerIds,
        $url = null,
        $data = null,
        $buttons = null,
        $schedule = null,
        $headings = "Notification Title",
        $subtitle = null
    );
});
