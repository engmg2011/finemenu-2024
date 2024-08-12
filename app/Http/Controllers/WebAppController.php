<?php

namespace App\Http\Controllers;


use App\Actions\WebAppAction;
use App\Models\Branch;
use App\Repository\Eloquent\RestaurantRepository;
use App\Repository\MenuRepositoryInterface;
use Illuminate\Http\JsonResponse;

class WebAppController extends Controller
{
    public function __construct(private readonly WebAppAction            $action,
                                private readonly RestaurantRepository    $restaurantRepository,
                                private readonly MenuRepositoryInterface $menuRepository)
    {

    }

    /**
     * Display a listing of the resource.
     *
     * @param $restaurantId
     * @return JsonResponse
     */
    public function nestedMenu($menuId): JsonResponse
    {
        $menu = $this->menuRepository->fullMenu($menuId);
        return response()->json($menu);
    }


    /**
     * Display a listing of the resource.
     *
     * @param $restaurantId
     * @return JsonResponse
     */
    public function branchMenu($branchSlug): JsonResponse
    {
        $branch = Branch::with(['locales', 'settings', 'media',
            'restaurant.locales', 'restaurant.media',
            'restaurant.settings'])->where('slug', $branchSlug)->firstOrFail();
        $menu = $this->menuRepository->fullMenu($branch->menu_id);
        return response()->json(compact('branch', 'menu'));
    }

    /**
     * Display a listing of the resource.
     *
     * @param $restaurantId
     * @return JsonResponse
     */
    public function dietRestaurant($restaurantId): JsonResponse
    {
        $menu = $this->restaurantRepository->dietMenu($restaurantId);
        return response()->json($menu);
    }

    public function version(): JsonResponse
    {
        return response()->json([
            "latest-version" => env("WEB_APP_LATEST_VERSION"),
            "should-update" => env("WEB_APP_SHOULD_UPDATE"),
            "must-update" => env("WEB_APP_MUST_UPDATE"),
            "min-acceptable-version" => env("WEB_APP_MIN_ACCEPTABLE_VERSION"),
        ]);
    }

}
