<?php

namespace App\Repository\Eloquent;


use App\Actions\MediaAction;
use App\Models\Category;
use App\Models\DietPlan;
use App\Models\Restaurant;
use App\Repository\RestaurantRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class RestaurantRepository extends BaseRepository implements RestaurantRepositoryInterface
{


    public function __construct(Restaurant                         $model,
                                private readonly MediaAction       $mediaAction,
                                private readonly LocaleRepository  $localeAction,
                                private readonly SettingRepository $settingAction
    )
    {
        parent::__construct($model);
    }

    public function processRestaurant(array $data): array
    {
        return array_only($data, ['name', 'user_id', 'passcode', 'type', 'creator_id', 'slug']);
    }

    public function createModel(array $data): Model
    {
        $model = $this->model->create($this->processRestaurant($data));
        if (isset($data['locales']))
            $this->localeAction->createLocale($model, $data['locales']);
        $this->setModel($model, $data);
        return $model;
    }

    public function setModel(&$model, &$data)
    {
        if (isset($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        if (isset($data['settings']))
            $this->settingAction->set($model, $data['settings']);
    }

    public function updateModel($id, array $data): Model
    {
        $model = tap($this->model->find($id))
            ->update($this->processRestaurant($data));
        if (isset($data['locales']))
            $this->localeAction->setLocales($model, $data['locales']);
        $this->setModel($model, $data);
        return $model;
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return Restaurant::with('media')->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function getModel(int $id)
    {
        return Restaurant::with(['media', 'settings', 'contents'])->find($id);
    }

    public function menu($restaurantId)
    {
        return Restaurant::with([
            'media', 'settings',
            'categories.locales', 'categories.media', 'categories.children.locales',
            'categories.children.media', 'categories.items.locales',
            'categories.items.addons.locales', 'categories.items.addons.children.locales', 'categories.items.discounts.locales',
            'categories.items.media', 'categories.items.prices.locales',
            'categories.children.items.locales', 'categories.children.items.media',
            'categories.children.items.prices.locales',
            'categories.children.items.addons.locales', 'categories.children.items.discounts.locales'
        ])->find($restaurantId);
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

}
