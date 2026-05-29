<?php

namespace App\Repository\Eloquent;


use App\Models\Page;
use App\Repository\PageRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class PageRepository extends BaseRepository implements PageRepositoryInterface
{
    public static array $modelRelations = ['locales', 'media'];

    public function __construct(Page $model, private LocaleRepository $localeAction)
    {
        parent::__construct($model);
    }

    public function process($businessId, $branchId, array $data)
    {
        $data['business_id'] = $businessId;
        $data['branch_id'] = $branchId;
        return array_only($data, ['business_id', 'branch_id', 'slug', 'user_id']);
    }

    public function listModel($businessId, $branchId)
    {
        return $this->model::with(['locales'])
            ->where('business_id', $businessId)
            ->where('branch_id', $branchId)
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function filter($businessId, $branchId)
    {
        $searchText = request('text');
        return $this->model::with(['locales'])
            ->where('business_id', $businessId)
            ->where(function ($query) use ($searchText) {
                if(!empty($searchText)) {
                    return $query->whereHas('locales', function ($query) use ($searchText) {
                        $query->where('name', 'LIKE', '%' . $searchText . '%')
                            ->orWhere('description', 'LIKE', '%' . $searchText . '%');
                    });
                }
            })
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function relations($model, $data)
    {
        if (isset($data['locales'])) {
            if (!$this->validateLocalesRelated($model, $data))
                abort(400,'Invalid Locales Data');
            $this->localeAction->setLocales($model, $data['locales']);
        }
    }

    public function createModel($businessId, $branchId, array $data): Model
    {
        $data['user_id'] = $data['user_id'] ?? auth('sanctum')->user()->id;
        $data['slug'] = locales_slug($data['locales']);
        $entity = $this->model->create($this->process($businessId,$branchId, $data));
        $this->relations($entity, $data);
        return $this->model->with(PageRepository::$modelRelations)->find($entity->id);
    }

    public function updateModel($businessId, $branchId, $id, array $data): Model
    {
        $model = tap($this->model->find($id))
            ->update($this->process($businessId,$branchId, $data));
        $this->relations($model, $data);
        return $this->model->with(PageRepository::$modelRelations)->find($model->id);
    }

    public function get($businessId, $branchId, int $id)
    {
        return $this->model->with(PageRepository::$modelRelations)->find($id);
    }

    public function destroy($businessId, $branchId, $id): ?bool
    {
        $this->model->where(['business_id' => $businessId])->find($id)->locales->map(fn($locale) => $locale->delete());
        return $this->delete($id);
    }

}
