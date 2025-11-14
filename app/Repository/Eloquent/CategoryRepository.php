<?php

namespace App\Repository\Eloquent;


use App\Actions\MediaAction;
use App\Constants\CategoryTypes;
use App\Models\Category;
use App\Models\Menu;
use App\Repository\CategoryRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{

    public function __construct(Category $model,
                                protected LocaleRepository $localeRepository,
                                private MediaAction        $mediaAction
    ) {
        parent::__construct($model);
    }

    public function process(array $data)
    {
        if(!isset($data['business_id']))
            $data['business_id'] = request()->route('businessId');
        return array_only($data, ['menu_id', 'parent_id', 'user_id', 'business_id', 'sort',
            'type', 'business_id','itemable_type','icon']);
    }

    public function list()
    {
        $businessId = request()->route('businessId');;
        $menuIds = Menu::where('business_id',$businessId)->pluck('id')->toArray();
        return $this->model::whereIn('menu_id',$menuIds)->with(['locales','media'])->orderBy('sort', 'asc')->paginate(request('per-page', 15));
    }

    public function featuresCategories()
    {
        $itemable_type = request()->get('itemable_type');
        $query = $this->model->query();

        if($itemable_type)
            $query->where('itemable_type', 'like', "%$itemable_type%");

        return $query->where('type', CategoryTypes::FEATURES)
            ->with(['locales','media','features.locales','features.feature_options.locales'])
            ->orderBy('sort', 'asc')
            ->paginate(request('per-page', 15));
    }

    public function createModel(array $data)
    {
        $data['user_id'] = $data['user_id'] ?? auth('sanctum')->user()->id;
        $category = $this->model->create($this->process($data));
        $this->localeRepository->createLocale($category, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($category, $data['media']);
        return $this->get($category->id);
    }


    public function updateModel($id, array $data): Model
    {
        unset($data['type']);
        $model = tap($this->model->find($id))
            ->update($this->process($data));
        $this->localeRepository->setLocales($model, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        return $this->get($id);
    }

    public function updateSort($data)
    {
        $sort = 1;
        foreach ($data['sortedIds'] as $id) {
            $this->model->find($id)->update(['sort' => $sort]);
            $sort++;
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function listModel()
    {
        return $this->model->list();
    }

    public function mainCategories()
    {
        return $this->model->where(['parent_id' => null])->get();
    }

    public function get(int $id)
    {
        return $this->model->with(['locales', 'media'])->find($id);
    }

    public function destroy($id): ?bool
    {
        $this->localeRepository->deleteEntityLocales($this->model->find($id));
        return $this->model->delete($id);
    }


    /**
     * @param $categories_names
     * @param $image_path
     * @return Collection
     */
    public function createCategoriesFromPath($categories_names,
                                             $image_path,
                                             $userId,
                                             $menuId
    ): Collection
    {
        $menu = Menu::find($menuId);
        $categories = new Collection();
        foreach ($categories_names as $category_name) {
            $lastCatId = $categories->count() ? $categories->last()->id : null;
            $same_category = $this->model->where('parent_id', $lastCatId)
                ->where('menu_id', $menuId)
                ->whereHas('locales', function ($q) use ($category_name) {
                    $q->where('name', $category_name);
                })->limit(1)->get();
            if ($same_category->count()) {
                $categories->push($same_category[0]);
                continue;
            }
            $lastCatId = $categories->count() ? $categories->last()->id : null;
            // TODO :: choose first of user locales
            $created_category = $this->create([
                "locales" => [["name" => $category_name, 'locale' => 'en'], ["name" => $category_name, 'locale' => 'ar']],
                "image" => $image_path,
                "user_id" => $userId,
                "business_id" => $menu->business_id,
                "menu_id" => $menuId,
                "parent_id" => $lastCatId]);
            $categories->push($created_category);
        }
        return $categories;
    }

}
