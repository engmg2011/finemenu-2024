<?php

namespace App\Repository\Eloquent\Itemable\Cars;


use App\Models\Items\Cars\CarBrand;
use App\Repository\Eloquent\BaseRepository;
use App\Repository\Eloquent\LocaleRepository;
use App\Repository\ItemableInterfaces\CarBrandRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class CarBrandRepository extends BaseRepository implements CarBrandRepositoryInterface
{
    public static array $modelRelations = ['locales', 'models.locales'];

    public function __construct(CarBrand $model, private LocaleRepository $localeAction)
    {
        parent::__construct($model);
    }

    public function process(array $data)
    {
        return array_only($data, ['icon', 'sort']);
    }

    public function relations($model, $data)
    {
        if (isset($data['locales'])) {
            if (!$this->validateLocalesRelated($model, $data))
                abort(400,'Invalid Locales Data');
            $this->localeAction->setLocales($model, $data['locales']);
        }
    }

    public function createModel(array $data): Model
    {
        $model = $this->model->create($this->process($data));
        $this->relations($model, $data);
        return $this->get($model->id);
    }

    public function updateModel($id, array $data): Model
    {
        $model = $this->model->find($id);
        $model->update($this->process($data));
        $this->relations($model, $data);
        return $this->get($model->id);
    }

    public function set(array $data): Model
    {
        if (isset($data['id']))
            return $this->updateModel($data['id'], $data);
        else
            return $this->create($data);
    }

    public function listModel()
    {
        return $this->model->with(self::$modelRelations)->get();
    }

    public function sort($data)
    {
        $sort = 1;
        foreach ($data['sortedIds'] as $id) {
            $this->whereId($id)->update(['sort' => $sort]);
            $sort++;
        }
        return true;
    }

    public function get(int $id)
    {
        return $this->with(self::$modelRelations)->find($id);
    }

    public function destroy($id): ?bool
    {
        $this->find($id)?->locales->map(
            fn($locale) => $locale->delete()
        );
        return $this->find($id)?->delete();
    }


}
