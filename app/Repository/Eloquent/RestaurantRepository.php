<?php

namespace App\Repository\Eloquent;


use App\Actions\MediaAction;
use App\Models\Category;
use App\Models\DietPlan;
use App\Models\Menu;
use App\Models\Restaurant;
use App\Repository\BranchRepositoryInterface;
use App\Repository\MenuRepositoryInterface;
use App\Repository\PermissionRepositoryInterface;
use App\Repository\RestaurantRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class RestaurantRepository extends BaseRepository implements RestaurantRepositoryInterface
{
    public static $modelRelations = ['locales', 'branches.locales', 'branches.menu.locales',
        'media', 'settings', 'contents', 'branches.settings', 'branches.menu.settings'];

    public function __construct(Restaurant                                     $model,
                                private readonly MediaAction                   $mediaAction,
                                private readonly LocaleRepository              $localeAction,
                                private readonly SettingRepository             $settingAction,
                                private readonly PermissionRepositoryInterface $permissionRepository,
                                private readonly BranchRepositoryInterface     $branchRepository,
                                private readonly MenuRepositoryInterface       $menuRepository
    )
    {
        parent::__construct($model);
    }

    public function processRestaurant(&$data): array
    {
        $data['user_id'] = $data['user_id'] ?? auth('api')->user()->id;
        if (!isset($data['name']) && isset($data['locales']))
            $data['name'] = $data['locales'][0]['name'];
        $data['slug'] = $this->menuRepository->createMenuId($data['name'], auth('api')->user()->email ?? $data['email']);
        return array_only($data, ['name', 'user_id', 'passcode', 'type', 'creator_id', 'slug']);
    }

    public function createModel(array $data): Model
    {
        $model = $this->model->create($this->processRestaurant($data));
        $this->setModelRelations($model, $data);

        // create menu
        $data['restaurant_id'] = $model->id;
        $menu = $this->menuRepository->createModel($model->id, $data);

        // create branch
        $data['menu_id'] = $menu->id;
        $this->branchRepository->createModel($model->id, $data);

        // give owner permissions
        $userId = $data['user_id'] ?? auth('api')->id();
        $this->permissionRepository->setRestaurantOwnerPermissions($userId, $model->id);

        return $model;
    }

    public function setModelRelations(&$model, &$data)
    {
        if (isset($data['locales']))
            $this->localeAction->setLocales($model, $data['locales']);
        if (isset($data['media']) && count($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        if (isset($data['settings']) && count($data['settings']))
            $this->settingAction->set($model, $data['settings']);
    }

    public function updateModel($id, array $data): Model
    {
        $model = tap($this->model->find($id))
            ->update($this->processRestaurant($data));
        $this->setModelRelations($model, $data);
        return $model;
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return Restaurant::with(RestaurantRepository::$modelRelations)
            ->where('user_id', auth('api')->id())
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }

    /**
     * @return mixed
     */
    public function allList()
    {
        return Restaurant::with(RestaurantRepository::$modelRelations)
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function getModel(int $id)
    {
        return Restaurant::with(RestaurantRepository::$modelRelations)->find($id);
    }

    public function dietMenu($restaurantId): array
    {
        $restaurant = Restaurant::with(['locales', 'media', 'settings'])->find($restaurantId);
        $plans = DietPlan::with(['locales', 'prices', 'prices.locales', 'media', 'discounts'])->where('restaurant_id', $restaurantId)->get();
        $categories = Category::whereNull('parent_id')
            ->with(['locales', 'media', 'children.locales', 'children.children.locales'])
            ->where('restaurant_id', $restaurantId)->get();
        return compact('restaurant', 'plans', 'categories');
    }

    public function registerNewRestaurantOwner(Request $request, $user)
    {
        $menuSlug = $this->menuRepository->createMenuId($request->businessName, $user->email);
        $businessData = [
            'user_id' => $user->id,
            'creator_id' => $user->id,
            'name' => $request->businessName,
            'email' => $request->email,
            'slug' => $menuSlug,
            "locales" => [["name" => $request->businessName, "locale" => "en"]]
        ];
        // create restaurant & assign owner permission
        $this->createModel($businessData);
    }

    public function destroy($id)
    {
        Menu::where('restaurant_id', $id)->delete();
        $this->model->find($id)->delete();

    }


}
