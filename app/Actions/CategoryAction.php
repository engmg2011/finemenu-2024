<?php

namespace App\Actions;


use App\Models\Category;
use App\Models\Menu;
use App\Repository\Eloquent\CategoryRepository;
use App\Repository\Eloquent\LocaleRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class CategoryAction
{

    public function __construct(
        protected LocaleRepository $localeRepository,
        private CategoryRepository $repository,
        private MediaAction        $mediaAction)
    {
    }

    public function process(array $data)
    {
        return array_only($data, ['menu_id', 'parent_id', 'user_id', 'business_id', 'sort']);
    }

    public function create(array $data)
    {
        $data['user_id'] = auth('api')->user()->id;
        $category = $this->repository->create($this->process($data));
        $this->localeRepository->createLocale($category, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($category, $data['media']);
        return $category;
    }


    public function update($id, array $data): Model
    {
        $model = tap($this->repository->find($id))
            ->update($this->process($data));
        $this->localeRepository->setLocales($model, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        return $model;
    }

    public function updateSort($data)
    {
        $sort = 1;
        foreach ($data['sortedIds'] as $id) {
            $this->repository->find($id)->update(['sort' => $sort]);
            $sort++;
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return $this->repository->list();
    }

    public function mainCategories()
    {
        return $this->repository->where(['parent_id' => null])->get();
    }

    public function get(int $id)
    {
        return Category::with(['locales', 'media'])->find($id);
    }

    public function destroy($id): ?bool
    {
        return $this->repository->delete($id);
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
            $same_category = Category::where('parent_id', $lastCatId)
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
                "locales" => [["name" => $category_name, 'locale' => 'en']],
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
