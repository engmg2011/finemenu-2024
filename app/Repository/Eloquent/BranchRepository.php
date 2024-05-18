<?php

namespace App\Repository\Eloquent;


use App\Models\Branch;
use App\Repository\BranchRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class BranchRepository extends BaseRepository implements BranchRepositoryInterface
{

    public function __construct(Branch $model, private LocaleRepository $localeAction)
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
        return array_only($data, ['restaurant_id', 'menu_id', 'sort']);
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
        return $this->model->with(BranchRepository::$modelRelations)->find($entity->id);
    }

    public function update($id, array $data): Model
    {
        $model = tap($this->model->find($id))
            ->update($this->process($data));
        $this->relations($model, $data);
        return $this->model->with(BranchRepository::$modelRelations)->find($model->id);
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


    public function get(int $id)
    {
        return $this->model->with(BranchRepository::$modelRelations)->find($id);
    }

    public function destroy($id): ?bool
    {
        $this->model->locales->map( fn($locale) => $locale->delete() );
        return $this->delete($id);
    }

}
