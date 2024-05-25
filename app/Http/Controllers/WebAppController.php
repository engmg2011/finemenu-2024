<?php

namespace App\Http\Controllers;


use App\Actions\WebAppAction;
use App\Models\Menu;
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
        $menu= Menu::with([
            'categories.locales' ,'categories.media' ,'categories.childrenNested.locales','categories.childrenNested.media',
            'categories.childrenNested.items' ,'categories.childrenNested.items.locales','categories.childrenNested.items.media',
            'settings', 'media'])->find($menuId);
        return response()->json($menu);
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


}
