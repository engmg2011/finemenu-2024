<?php

namespace App\Repository\Eloquent;



use App\Models\Feature;
use App\Repository\FeatureRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class FeatureRepository extends BaseRepository implements FeatureRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Feature $model
     * @param LocaleRepository $localeRepository
     */
    public function __construct(Feature $model, private LocaleRepository $localeRepository,
    private FeatureOptionsRepository $featureOptionsRepository) {
        parent::__construct($model);
    }

    public static array $modelRelations = ['locales', 'feature_options.locales'];

    public function process(array $data)
    {
        return array_only($data, ['key', 'type','itemable_type','sort', "icon", "icon-font-type","color", "category_id", "featured"]);
    }

    public function processFeaturable(array $data)
    {
        if(!isset($data['category_id']))
            $data['category_id'] = Feature::find($data['id'])->category_id;
        return array_only($data, ['value', 'value_unit','sort' , 'category_id']);
    }

    public function relations($model, $data)
    {
        if (isset($data['locales'])) {
            if (!$this->validateLocalesRelated($model, $data))
                abort(400,'Invalid Locales Data');
            $this->localeRepository->setLocales($model, $data['locales']);
        }
        if (isset($data['feature_options'])) {
            $this->featureOptionsRepository->setOptions($model, $data['feature_options']);
        }
    }

    public function createModel(array $data): Model
    {
        $maxSort = $this->model->max('sort') ?? 0;
        $data['sort'] = $maxSort + 1;
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

    public function listModel($itemable_type)
    {
        $query = $this->model->query();
        if($itemable_type)
            $query->where('itemable_type','like', '%'.$itemable_type.'%');
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


    public function setFeatures($model, $features)
    {
        $syncData = [];
        foreach ($features as $feature) {
            $syncData[$feature['id']] = $this->processFeaturable($feature);
        }
        $model->features()->sync($syncData);
    }

}
