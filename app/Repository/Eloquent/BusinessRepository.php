<?php

namespace App\Repository\Eloquent;


use App\Actions\MediaAction;
use App\Actions\SubscriptionAction;
use App\Models\Business;
use App\Models\Category;
use App\Models\DietPlan;
use App\Models\Item;
use App\Models\Package;
use App\Models\User;
use App\Repository\BusinessRepositoryInterface;
use App\Repository\MenuRepositoryInterface;
use App\Services\BusinessService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class BusinessRepository extends BaseRepository implements BusinessRepositoryInterface
{
    public static $modelRelations = ['locales', 'branches.locales', 'branches.menu.locales',
        'media', 'settings', 'contents', 'branches.settings', 'branches.menu.settings'];

    public function __construct(Business                                 $model,
                                private readonly MediaAction             $mediaAction,
                                private readonly LocaleRepository        $localeAction,
                                private readonly SettingRepository       $settingAction,
                                private readonly BusinessService         $businessService,
                                private readonly MenuRepositoryInterface $menuRepository,
                                private readonly SubscriptionAction      $subscriptionAction,
                                private readonly PermissionRepository    $permissionRepository,
                                private readonly PriceRepository         $priceRepository,
                                private readonly ItemRepository          $itemRepository,
                                private readonly BranchRepository        $branchRepository,
                                private readonly CategoryRepository      $categoryRepository
    )
    {
        parent::__construct($model);
    }

    public function process(&$data): array
    {
        return array_only($data, ['name', 'user_id', 'passcode', 'creator_id', 'slug', 'type']);
    }

    public function createModel(array $data): Model
    {
        $data['user_id'] = $data['user_id'] ?? auth('sanctum')->user()->id;
        if (!isset($data['name']) && isset($data['locales']))
            $data['name'] = $data['locales'][0]['name'];
        $data['slug'] = $this->menuRepository->createMenuId($data['name'], auth('sanctum')->user()->email ?? $data['email']);

        $model = $this->model->create($this->process($data));
        $this->setModelRelations($model, $data);

        // Give permission to owner
        $userId = $data['user_id'] ?? auth('sanctum')->id();
        $this->permissionRepository->createBusinessPermission($model->id, User::find($userId));

        // business type different from menu type
        unset($data['type']);
        $this->businessService->createMenuAndBranch($model, $data);

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
            ->update($this->process($data));
        $this->setModelRelations($model, $data);
        return $model;
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return Business::with(BusinessRepository::$modelRelations)
            ->where('user_id', auth('sanctum')->id())
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }

    /**
     * @return mixed
     */
    public function businessList()
    {
        return Business::with(BusinessRepository::$modelRelations)
            ->where(function ($query) {
                if (\request('type'))
                    $query->where('type', request('type'));
            })
            ->where('app_hidden', false)
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function getModel(int $id)
    {
        return Business::with(BusinessRepository::$modelRelations)->find($id);
    }

    public function dietMenu($menu, $branch): array
    {
        $business = Business::with(['locales', 'media', 'settings'])->find($menu->business_id);
        $plans = DietPlan::with(['locales', 'prices', 'prices.locales', 'media', 'discounts'])
            ->find($menu->id);
        $categories = Category::whereNull('parent_id')
            ->with(['locales', 'media', 'children.locales', 'children.children.locales'])
            ->where('menu_id', $menu->id)->get();
        return compact('business', 'branch', 'plans', 'categories');
    }

    public function registerNewOwner(Request $request, $user)
    {
        $businessData = $this->businessService->registerationBusinessData($request, $user);
        // create restaurant & assign owner permission
        $business = $this->createModel($businessData);
        $user->update(['business_id' => $business->id]);
    }

    public function deleteItem(Item $item)
    {
        foreach ($item->media as $mediaItem) {
            $this->mediaAction->delete($mediaItem->id);
        }
        foreach ($item->prices as $price) {
            $this->priceRepository->destroy($price->id);
        }
        $this->itemRepository->destroy($item->id);
    }

    public function destroy($id)
    {
        if(auth()->user()->email !== "eng.mg2011@gmail.com")
            abort(403);

        $business = Business::with(BusinessRepository::$modelRelations)->find($id);
        if($business){
            // Delete Menus
            $business->menus->map(function ($menu) {
                // Delete Categories
                $menu->categories->map(function (Category $category) {
                    // Delete Categories children
                    $category->children->map(function (Category $child) {
                        $child->items->map(function ($item) {
                            $this->deleteItem($item);
                        });
                        $this->categoryRepository->destroy($child->id);
                    });
                    $category->items->map(function ($item) {
                        $this->deleteItem($item);
                    });
                    $this->categoryRepository->destroy($category->id);
                });
            });
            // Delete Branches
            $business->branches->map(function ($branch) use ($id) {
                $this->branchRepository->destroy( $id , $branch->id);
            });
            // Delete owner
            $business->user->delete();
            // Delete Locales
            $this->localeAction->deleteEntityLocales($business);
            // Delete Business
            $this->model->delete();
            return "Deleted successfully";
        }
        return "Not found";
    }


    /**
     * @param $user
     * @return void
     */
    public function createSubscription($user): void
    {
        // Create subscription and assign trial package
        $package = Package::where('slug', 'trial')->first();
        $expiry = (new Carbon())->addDays($package->days)->format('Y-m-d H:i:s');
        $this->subscriptionAction->create(['creator_id' => $user->id, 'user_id' => $user->id,
            'package_id' => $package->id, 'from' => Carbon::now(), 'to' => $expiry]);
    }
}
