<?php

namespace App\Http\Controllers;

use App\Actions\RestaurantAction;
use App\Actions\WebAppAction;
use Illuminate\Http\JsonResponse;

class WebAppController extends Controller
{
    public function __construct(private readonly WebAppAction     $action,
                                private readonly RestaurantAction $restaurantAction)
    {

    }

    /**
     * Display a listing of the resource.
     *
     * @param $restaurantId
     * @return JsonResponse
     */
    public function restaurant($restaurantId): JsonResponse
    {
        $menu = $this->restaurantAction->menu($restaurantId);
        $response = [];
        $response['categories'] = [];
        foreach ($menu->categories as &$mainCategory) {
            $categoryItems = $this->action->getNestedItems($mainCategory);
            unset($mainCategory->children);
            unset($mainCategory->items);
            foreach ($categoryItems as &$item)
                $item->category_id = $mainCategory->id;
            $mainCategory->items = $categoryItems;
            $response['categories'][] = $mainCategory;
        }
        $response['settings'] = $menu->settings;
        $response['media'] = $menu->media;
        return response()->json($response);

    }


    /**
     * Display a listing of the resource.
     *
     * @param $restaurantId
     * @return JsonResponse
     */
    public function dietRestaurant($restaurantId): JsonResponse
    {
        $menu = $this->restaurantAction->dietMenu($restaurantId);
        return response()->json($menu);
    }


}
