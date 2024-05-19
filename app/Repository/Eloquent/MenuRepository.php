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

    public function list()
    {
        return $this->model::with(['locales'])
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }


    public static array $modelRelations = ['locales'];


    public function process(array $data)
    {
        return array_only($data, ['slug', 'restaurant_id', 'sort', 'user_id']);
    }

    public function relations($model, $data)
    {
        if (isset($data['locales'])) {
            if (!$this->validateLocalesRelated($model, $data))
                throw new \Exception('Invalid Locales Data');
            $this->localeAction->setLocales($model, $data['locales']);
        }
    }

    public function createModel(array $data): Model
    {
        $entity = $this->model->create($this->process($data));
        $this->relations($entity, $data);
        return $this->model->with(MenuRepository::$modelRelations)->find($entity->id);
    }

    public function update($id, array $data): Model
    {
        $model = tap($this->model->find($id))
            ->update($this->process($data));
        $this->relations($model, $data);
        return $this->model->with(MenuRepository::$modelRelations)->find($model->id);
    }

    public function sort($data)
    {
        $sort = 1;
        foreach ($data['sortedIds'] as $id) {
            $this->model->whereId($id)->update(['sort' => $sort]);
            $sort++;
        }
        return true;
    }


    public function menu($restaurantId)
    {
        return Menu::with(['media', 'settings',
            'categories.locales', 'categories.media', 'categories.children.locales',
            'categories.children.media', 'categories.items.locales',
            'categories.items.addons.locales', 'categories.items.addons.children.locales', 'categories.items.discounts.locales',
            'categories.items.media', 'categories.items.prices.locales',
            'categories.children.items.locales', 'categories.children.items.media',
            'categories.children.items.prices.locales',
            'categories.children.items.addons.locales', 'categories.children.items.discounts.locales'
        ])->find($restaurantId);
    }

    public function get(int $id)
    {

        return $this->model->with(MenuRepository::$modelRelations)->find($id);
    }

    public function destroy($id): ?bool
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
