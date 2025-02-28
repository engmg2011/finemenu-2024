<?php

namespace App\Http\Controllers;

use App\Actions\MediaAction;
use App\Http\Resources\DataResource;
use App\Models\Category;
use App\Models\User;
use App\Repository\UserRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class UsersController extends Controller
{

    public function __construct(public UserRepositoryInterface $userRepository, private MediaAction $mediaAction)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index($businessId)
    {
        return DataResource::collection($this->userRepository->listModel($businessId));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        $data = $request->all();
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'password' => 'required|confirmed|min:8',
            'email' => ['string', 'email', 'max:255', 'unique:users'],
            'phone' => ['string', 'min:8', 'max:15', 'unique:users'],
            'phone_required' => Rule::requiredIf(fn() => !isset($data['email']) && !isset($data['phone'])),
        ], [
            'phone_required' => "phone or email required"
        ]);
        if ($validator->fails())
            return response()->json(['message' => 'error occurred', 'errors' => $validator->errors()], 400);

        $data['business_id'] = $request->route('businessId');
        return \response()->json($this->userRepository->createModel($data));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function search(Request $request)
    {
        $businessId = request()->route('businessId');
        return \response()->json($this->userRepository->search($businessId, $request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        return \response()->json($this->userRepository->get($id));
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

        if (isset($data['password']) && $data['password'] === "")
            unset($data['password']);


        $validator = Validator::make($data, [
            'email' => ['string', 'email', 'max:255',
                Rule::unique('users')->ignore($id)],
            'phone' => ['string', 'min:8', 'max:15', 'unique:users'],
            'name' => ['string', 'max:255'],
            'password' => ['sometimes', 'string', 'confirmed', 'min:8'],
        ]);
        if ($validator->fails())
            return response()->json(['message' => 'error occurred', 'errors' => $validator->errors()], 403);

        if (isset($data['media']) && count($data['media']) > 0) {
            $data['media'][0]['slug'] = 'profile-picture';
            $user = User::find($id);
            $this->mediaAction->setMedia($user, $data['media']);
        }
        return \response()->json($this->userRepository->updateModel($id, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        return \response()->json($this->userRepository->destroy($id));
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
            'business.branches.floors.locales',
            'devices'
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
        return response()->json($this->userRepository->menu($businessId));
    }


    public function createLoginQr()
    {
        $businessId = \request()->route('businessId');
        $branchId = \request()->route('modelId');
        return $this->userRepository->createLoginQr($businessId, $branchId);
    }

    public function loginByQr(Request $request)
    {
        return $this->userRepository->loginByQr($request);
    }

}
