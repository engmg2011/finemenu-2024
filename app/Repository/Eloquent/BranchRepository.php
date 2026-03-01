<?php

namespace App\Repository\Eloquent;


use App\Models\Branch;
use App\Models\Locales;
use App\Models\User;
use App\Repository\BranchRepositoryInterface;
use App\Repository\PermissionRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class BranchRepository extends BaseRepository implements BranchRepositoryInterface
{

    public function __construct(Branch $model, private LocaleRepository $localeAction, private PermissionRepositoryInterface $permissionRepository)
    {
        parent::__construct($model);
    }

    public static array $modelRelations = ['locales'];


    public function process(array $data)
    {
        return array_only($data, ['business_id', 'menu_id', 'sort', 'slug']);
    }

    public function relations($model, $data)
    {
        if (isset($data['locales'])) {
            if (!$this->validateLocalesRelated($model, $data))
                abort(400,'Invalid Locales Data');
            $this->localeAction->setLocales($model, $data['locales']);
        }
    }

    public function createModel($businessId, array $data): Model
    {
        $data['business_id'] = $businessId;
        $entity = $this->model->create($this->process($data));
        $this->relations($entity, $data);
        // Give permission to owner for branch "branch.{id}"
        $userId = $data['user_id'] ?? auth('sanctum')->id();
        $this->permissionRepository->createBranchPermission($entity->id, User::find($userId));
        $this->permissionRepository->createBranchServicePermissions($entity->id);
        // return branch data
        return $this->model->with(BranchRepository::$modelRelations)->find($entity->id);
    }

    public function updateModel($businessId, $id, array $data): Model
    {
        $data['business_id'] = $businessId;
        $model = $this->model->find($id);
        $this->relations($model, $data);
        $model->update($this->process($data));
        return $this->model->with(BranchRepository::$modelRelations)->find($model->id);
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


    public function get($businessId, int $id)
    {
        return $this->model->where(['business_id' => $businessId])->with(BranchRepository::$modelRelations)->find($id);
    }

    public function destroy($businessId, $id): ?bool
    {
        $this->model->where(['business_id' => $businessId])->find($id)?->locales->map(
            fn($locale) => $locale->delete()
        );
        return $this->model->where([
            'business_id' => $businessId,
            'id' => $id
        ])?->delete();
    }

    public function backup($businessId)
    {
        $branches = Branch::where('business_id', $businessId)->get();
        $locales =  Locales::where(['localizable_type' => Branch::class])
                ->whereIn('localizable_id',$branches->pluck('id')->toArray())->get() ;

        return compact('branches', 'locales');
    }

}
