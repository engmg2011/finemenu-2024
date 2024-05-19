<?php

namespace App\Http\Controllers;


use App\Actions\WebAppAction;
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
    public function restaurant($restaurantId): JsonResponse
    {
        $restaurant = $this->restaurantRepository->getModel($restaurantId);
        $menuId = $restaurant['branches'][0]['branchId'] ?? null;
        $response = [];
        if ($menuId) {
            $menu = $this->menuRepository->get($menuId);
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
        $menu = $this->restaurantRepository->dietMenu($restaurantId);
        return response()->json($menu);
    }


}
