<?php

namespace App\Http\Controllers;

use App\Actions\UserAction;
use App\Http\Resources\DataResource;
use App\Models\Category;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;

class UsersController extends Controller
{

    public function __construct(public UserAction $action)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return DataResource::collection($this->action->list());
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        return \response()->json($this->action->create($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        return \response()->json($this->action->get($id));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        return \response()->json($this->action->update($id,$request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        return \response()->json($this->action->destroy($id));
    }


    public function info(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        if(!$userId)
            return response()->json(['message' => 'Not authorized'], 401);

        $user = User::with([
            'restaurants.locales',
            'restaurants.menus.locales',
            'restaurants.branches.locales',
            'restaurants.branches.floors.locales',
            'hotels.locales',
        ])->find($userId);
        $user['token'] = $user->createToken('authToken')->accessToken;
        return response()->json($user);
    }

    /**
     * TODO :: should be removed after finishing new UI
     * @param $userId
     * @return JsonResponse
     */
    public function userItems($userId): JsonResponse
    {
        $categories = Category::where('user_id', $userId)
            ->with(['items.locales','items.media','items.prices.locales','items.addons', 'items.discounts'])
            ->orderBy('sort','asc')
            ->paginate(50);
        $items = [];
        foreach ($categories as $category){
            foreach ($category->items as $item){
                $items[] = $item;
            }
        }
        return \response()->json($items);
    }

    public function menu($restaurantId) {
        return response()->json($this->action->menu($restaurantId));
    }
}
