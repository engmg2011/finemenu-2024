<?php

namespace App\Repository\Eloquent;



use App\Models\Feature;
use App\Models\FeatureOptions;
use App\Repository\FeatureOptionsRepositoryInterface;
use App\Repository\FeatureRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class FeatureOptionsRepository extends BaseRepository implements FeatureOptionsRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param FeatureOptions $model
     * @param LocaleRepository $localeRepository
     */
    public function __construct(FeatureOptions $model, private LocaleRepository $localeRepository) {
        parent::__construct($model);
    }

    public static array $modelRelations = ['locales'];

    public function process(array $data)
    {
        return array_only($data, ['feature_id','sort']);
    }

    public function relations($model, $data)
    {
        if (isset($data['locales'])) {
            if (!$this->validateLocalesRelated($model, $data))
                abort(400,'Invalid Locales Data');
            $this->localeRepository->setLocales($model, $data['locales']);
        }
    }

    public function createModel(array $data): Model
    {
        $entity = $this->model->create($this->process($data));
        $this->relations($entity, $data);
        return $this->model->with(self::$modelRelations)->find($entity->id);
    }

    public function updateModel($id, array $data): Model
    {
        $model = $this->find($id);
        if(!$model)
            abort(400,"Error: no data exist with the same id");
        $model->update($this->process($data));
        $this->relations($model, $data);
        return $this->model->with(self::$modelRelations)->find($model->id);
    }

    public function listModel()
    {
        $query = $this->model->query();
        return $query->with(self::$modelRelations)->get();
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

    public function setOptions($model, $options)
    {
        foreach ($options as &$option) {
            if(isset($option['id'])){
                $this->updateModel($option['id'], $option);
            }
            else{
                $option['feature_id'] = $model['id'];
                $option['feature_type'] = get_class($model);
                $this->createModel($option);
            }

        }
    }

}
