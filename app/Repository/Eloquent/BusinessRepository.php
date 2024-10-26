<?php

namespace App\Repository\Eloquent;


use App\Actions\MediaAction;
use App\Actions\SubscriptionAction;
use App\Models\Business;
use App\Models\Category;
use App\Models\DietPlan;
use App\Models\Menu;
use App\Models\Package;
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
                                private readonly SubscriptionAction      $subscriptionAction
    )
    {
        parent::__construct($model);
    }

    public function process(&$data): array
    {
        $data['user_id'] = $data['user_id'] ?? auth('sanctum')->user()->id;
        if (!isset($data['name']) && isset($data['locales']))
            $data['name'] = $data['locales'][0]['name'];
        $data['slug'] = $this->menuRepository->createMenuId($data['name'], auth('sanctum')->user()->email ?? $data['email']);
        return array_only($data, ['name', 'user_id', 'passcode', 'creator_id', 'slug']);
    }

    public function createModel(array $data): Model
    {
        $model = $this->model->create($this->process($data));
        $this->setModelRelations($model, $data);

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
                if(\request('type'))
                $query->where('type', request('type'));
            })
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
        $this->createModel($businessData);
    }

    public function destroy($id)
    {
        $this->localeAction->deleteEntityLocales(Business::find($id));
        Menu::where('business_id', $id)->delete();
        $this->model->find($id)->delete();

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
