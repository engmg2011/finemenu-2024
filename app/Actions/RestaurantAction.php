<?php


namespace App\Actions;

use App\Models\Category;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Repository\Eloquent\RestaurantRepository;
use Illuminate\Database\Eloquent\Model;

class RestaurantAction
{

    public function __construct(private RestaurantRepository $repository, private MediaAction $mediaAction,
                                private LocaleAction         $localeAction, private SettingAction $settingAction)
    {
    }

    public function processRestaurant(array $data): array
    {
        return array_only($data, ['name', 'user_id', 'passcode', 'creator_id', 'slug']);
    }

    public function createModel(array $data): Model
    {
        $model = $this->repository->create($this->processRestaurant($data));
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
        $model = tap($this->repository->find($id))
            ->update($this->processRestaurant($data));
        if (isset($data['locales']))
            $this->localeAction->updateLocales($model, $data['locales']);
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
        $plans = Plan::with(['locales', 'prices', 'media', 'discounts'])->where('restaurant_id', $restaurantId)->get();
        $categories = Category::whereNull('parent_id')
                    ->with(['locales', 'media', 'children.locales', 'children.children.locales'])
                    ->where('restaurant_id', $restaurantId)->get();
        return compact('restaurant','plans', 'categories');
    }

}
