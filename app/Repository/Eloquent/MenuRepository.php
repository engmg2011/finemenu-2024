<?php

namespace App\Repository\Eloquent;


use App\Models\Menu;
use App\Repository\MenuRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class MenuRepository extends BaseRepository implements MenuRepositoryInterface
{

    public function __construct(Menu $model, private LocaleRepository $localeAction)
    {
        parent::__construct($model);
    }

    public function listModel($restaurantId)
    {
        return $this->model::with(['locales'])
            ->where('restaurant_id', $restaurantId)
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public static array $modelRelations = ['locales'];

    public function process($restaurantId, array $data)
    {
        $data['user_id'] = $data['user_id'] ?? auth('api')->user()->id;
        if(!isset($data['name']) && isset($data['locales']))
            $data['name'] = $data['locales'][0]['name'];
        $data['slug'] = $this->createMenuId($data['name'], auth('api')->user()->email);
        return array_only($data, ['slug', 'name', 'restaurant_id', 'sort', 'user_id']);
    }

    public function relations($model, $data)
    {
        if (isset($data['locales'])) {
            if (!$this->validateLocalesRelated($model, $data))
                throw new \Exception('Invalid Locales Data');
            $this->localeAction->setLocales($model, $data['locales']);
        }
    }

    public function createModel($restaurantId, array $data): Model
    {
        $entity = $this->model->create($this->process($restaurantId, $data));
        $this->relations($entity, $data);
        return $this->model->with(MenuRepository::$modelRelations)->find($entity->id);
    }

    public function updateModel($restaurantId, $id, array $data): Model
    {
        $model = tap($this->model->find($id))
            ->update($this->process($restaurantId, $data));
        $this->relations($model, $data);
        return $this->model->with(MenuRepository::$modelRelations)->find($model->id);
    }

    public function sort($restaurantId, $data)
    {
        $sort = 1;
        foreach ($data['sortedIds'] as $id) {
            $this->model->whereId($id)->update(['sort' => $sort]);
            $sort++;
        }
        return true;
    }


    public function fullMenu($id)
    {
        return Menu::with([
            'settings', 'media', 'locales',
            'categories.locales' ,'categories.media' ,
            'categories.items.locales',
            'categories.items.addons.locales',
            'categories.items.addons.children.locales',
            'categories.items.discounts.locales',
            'categories.items.media',
            'categories.items.prices.locales',
            'categories.childrenNested.locales',
            'categories.childrenNested.media',
            'categories.childrenNested.items.locales',
            'categories.childrenNested.items.media',
            'categories.childrenNested.items.prices.locales',
            'categories.childrenNested.items.addons.locales',
            'categories.childrenNested.items.discounts.locales'
        ])->find($id);
    }

    public function get($restaurantId, int $id)
    {

        return $this->model->with(MenuRepository::$modelRelations)->find($id);
    }

    public function destroy($restaurantId, $id): ?bool
    {
        $this->model->locales->map(fn($locale) => $locale->delete());
        return $this->delete($id);
    }

    public function createMenuId(string $businessName, string|null $email = null): string
    {
        $businessName = str_replace(' ', '-', $businessName);
        $businessName = str_replace('.', '-', $businessName);
        $businessName = urlencode($businessName);
        $businessName = strtolower($businessName);
        $count = $this->model->where('slug', $businessName)->count();
        if ($count === 0)
            return $businessName;
        return slug($businessName);
    }

}
