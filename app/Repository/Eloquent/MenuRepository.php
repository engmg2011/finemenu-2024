<?php

namespace App\Repository\Eloquent;


use App\Constants\BusinessTypes;
use App\Models\Category;
use App\Models\Menu;
use App\Repository\MenuRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class MenuRepository extends BaseRepository implements MenuRepositoryInterface
{

    public function __construct(Menu $model, private LocaleRepository $localeAction)
    {
        parent::__construct($model);
    }

    public function listModel($businessId)
    {
        return $this->model::with(['locales'])
            ->where('business_id', $businessId)
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public static array $modelRelations = ['locales'];

    public function process($businessId, array $data)
    {
        $data['user_id'] = $data['user_id'] ?? auth('sanctum')->user()->id;
        if (!isset($data['name']) && isset($data['locales']))
            $data['name'] = $data['locales'][0]['name'];
        $data['slug'] = $this->createMenuId($data['name'], auth('sanctum')->user()->email ?? $data['email']);
        return array_only($data, ['slug', 'name', 'business_id', 'sort', 'user_id', 'type']);
    }

    public function relations($model, $data)
    {
        if (isset($data['locales'])) {
            if (!$this->validateLocalesRelated($model, $data))
                throw new \Exception('Invalid Locales Data');
            $this->localeAction->setLocales($model, $data['locales']);
        }
    }

    public function createModel($businessId, array $data): Model
    {
        $entity = $this->model->create($this->process($businessId, $data));
        $this->relations($entity, $data);
        return $this->model->with(MenuRepository::$modelRelations)->find($entity->id);
    }

    public function updateModel($businessId, $id, array $data): Model
    {
        $model = tap($this->model->find($id))
            ->update($this->process($businessId, $data));
        $this->relations($model, $data);
        return $this->model->with(MenuRepository::$modelRelations)->find($model->id);
    }

    public function sort($businessId, $data)
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
        return Menu::with(['settings', 'media', 'locales',
            'categories.childrenNested',
            'categories.locales',
            'categories.settings',
            'categories.media',
            'categories.items.locales',
            'categories.items.addons.locales',
            'categories.items.addons.locales',
            'categories.items.discounts.locales',
            'categories.items.media',
            'categories.items.holidays.locales',
            'categories.items.prices.locales',
            'categories.items.itemable'
            ])->find($id);
    }

    public function get($businessId, int $id)
    {
        return $this->model->with(MenuRepository::$modelRelations)->find($id);
    }

    public function destroy($businessId, $id): ?bool
    {
        $this->model->where(['business_id' => $businessId])->find($id)->locales->map(fn($locale) => $locale->delete());
        return $this->delete($id);
    }

    public function createMenuId(string $businessName, string|null $email = null): string
    {
        $businessName = str_replace(' ', '-', $businessName);
        $businessName = str_replace('.', '-', $businessName);
        $businessName = str_replace('@', '-', $businessName);
        $businessName = urlencode($businessName);
        $businessName = strtolower($businessName);
        $count = $this->model->where('slug', $businessName)->count();
        if ($count === 0)
            return $businessName;
        return slug($businessName);
    }

}
