<?php

namespace App\Repository\Eloquent;


use App\Models\Table;
use App\Repository\TableRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TableRepository extends BaseRepository implements TableRepositoryInterface
{

    public function __construct(Table $model, private LocaleRepository $localeAction)
    {
        parent::__construct($model);
    }

    private function table($floorId): Builder
    {
        return $this->model->where([
            'floor_id' => $floorId
        ]);
    }

    public function list()
    {
        return $this->model::with(['locales'])
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }


    public static array $modelRelations = ['locales'];


    public function process($floorId, array $data)
    {
        $data['floor_id'] = $floorId;
        return array_only($data, ['floor_id', 'sort']);
    }

    public function relations($model, $data)
    {
        // TODO :: Check all locales related to the same model

        if (isset($data['locales'])) {
            if (!$this->validateLocalesRelated($model, $data))
                throw new \Exception('Invalid Locales Data');

            $this->localeAction->setLocales($model, $data['locales']);
        }
    }

    public function createModel($floorId, array $data): Model
    {
        $entity = $this->model->create($this->process($floorId, $data));
        $this->relations($entity, $data);
        return $this->model->with(TableRepository::$modelRelations)->find($entity->id);
    }

    public function updateModel($floorId, $id, array $data): Model
    {
        $model = $this->table($floorId)->find($id);
        if (!$model)
            throw new \Exception("Error: no table exists with the same id");
        $model->update($this->process($floorId, $data));
        $this->relations($model, $data);
        return $this->model->with(TableRepository::$modelRelations)->find($model->id);
    }

    public function sort($floorId, $data)
    {
        $sort = 1;
        foreach ($data['sortedIds'] as $id) {
            $this->model->whereId($id)->update(['sort' => $sort]);
            $sort++;
        }
        return true;
    }


    public function get($floorId, int $id)
    {
        return $this->table($floorId)->with(TableRepository::$modelRelations)->find($id);
    }

    public function destroy($floorId, $id): ?bool
    {
        $this->table($floorId)->find($id)?->locales->map(
            fn($locale) => $locale->delete()
        );
        return $this->table($floorId)->find($id)?->delete();
    }

    public function floorTables($floorId)
    {
        return $this->listWhere(
            ['floor_id' => $floorId],
            ['locales']
        );
    }

}
