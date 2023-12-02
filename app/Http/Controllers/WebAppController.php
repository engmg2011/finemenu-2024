<?php

namespace App\Http\Controllers;

use App\Actions\RestaurantAction;
use App\Actions\WebAppAction;
use Illuminate\Http\Request;

class WebAppController extends Controller
{
    public function __construct(private WebAppAction     $action,
                                private RestaurantAction $restaurantAction)
    {

    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index($restaurantId)
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
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
