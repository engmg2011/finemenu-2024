<?php

namespace App\Http\Controllers;

use App\Actions\MediaAction;
use App\Actions\UserAction;
use App\Constants\UserTypes;
use App\Http\Resources\DataResource;
use App\Models\Category;
use App\Models\User;
use Auth;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use function PHPUnit\Framework\isEmpty;

class UsersController extends Controller
{

    public function __construct(public UserAction $action, private MediaAction $mediaAction)
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
     * @param int $id
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
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        $data = $request->all();
        unset($data['email']);
        unset($data['phone']);
        if( $data['password'] === "" || $data['password'] === null)
            unset($data['password']);

        $validator = Validator::make($data, [
            'email' => ['string', 'email', 'max:255', 'unique:users'],
            'phone' => ['string', 'min:8', 'max:15', 'unique:users'],
            'name' => ['string', 'max:255'],
            'password' => ['sometimes', 'string', 'confirmed' , 'min:8'],
        ]);
        if ($validator->fails())
            return response()->json(['message' => 'error occurred', 'errors' => $validator->errors()], 403);

        if(isset($data['media']) && count($data['media']) > 0){
            $data['media'][0]['slug'] = 'profile-picture';
            $user = User::find($id);
            $this->mediaAction->setMedia( $user ,$data['media']);
        }
        return \response()->json($this->action->updateModel($id, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        return \response()->json($this->action->destroy($id));
    }


    public function info(Request $request): JsonResponse
    {
        $userId = $request->user()->id;
        if (!$userId)
            return response()->json(['message' => 'Not authorized'], 401);

        $user = User::with([
            'media',
            'business.locales',
            'business.menus.locales',
            'business.branches.locales',
            'business.branches.floors.locales'
        ])->find($userId);
        $user['token'] = $user->createToken('authToken')->plainTextToken;
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
            ->with(['items.locales', 'items.media', 'items.prices.locales', 'items.addons', 'items.discounts'])
            ->orderBy('sort', 'asc')
            ->paginate(50);
        $items = [];
        foreach ($categories as $category) {
            foreach ($category->items as $item) {
                $items[] = $item;
            }
        }
        return \response()->json($items);
    }

    public function menu($businessId)
    {
        return response()->json($this->action->menu($businessId));
    }


    public function createLoginQr()
    {
        $businessId = \request()->route('businessId');
        $branchId = \request()->route('modelId');
        return $this->action->createLoginQr($businessId, $branchId);
    }

    public function loginByQr(Request $request)
    {
        return $this->action->loginByQr($request);
    }

}
