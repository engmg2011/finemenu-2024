<?php

namespace App\Repository\Eloquent;

use App\Actions\DiscountAction;
use App\Actions\LocaleAction;
use App\Actions\MediaAction;
use App\Actions\PriceAction;
use App\Models\DietPlan;
use App\Repository\DietPlanRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DietPlanRepository extends BaseRepository implements DietPlanRepositoryInterface
{
    public array $relations = ['locales', 'prices', 'media', 'discounts'];

    /**
     * UserRepository constructor.
     * @param DietPlan $model
     * @param MediaAction $mediaAction
     * @param LocaleAction $localeAction
     * @param PriceAction $priceAction
     * @param DiscountAction $discountAction
     */
    public function __construct(DietPlan                        $model,
                                private readonly MediaAction    $mediaAction,
                                private readonly LocaleAction   $localeAction,
                                private readonly PriceAction    $priceAction,
                                private readonly DiscountAction $discountAction)
    {
        parent::__construct($model);
    }

    public function processPlan(array $data): array
    {
        $data['user_id'] = auth('api')->user()->id;
        return array_only($data, ['restaurant_id', 'category_id', 'user_id', 'item_ids']);
    }

    /**
     * @param int $id
     * @return Builder|DietPlan
     */
    public function getModel(int $id, array $extraRelations = []): Builder|DietPlan
    {
        $allRelations = array_merge($this->relations , $extraRelations);
        return DietPlan::with($allRelations)->find($id);
    }

    public function relationsProcess(&$model, &$data): void{
        if (isset($data['locales']))
            $this->localeAction->updateLocales($model, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        if (isset($data['prices']))
            $this->priceAction->setPrices($model, $data['prices']);
        if (isset($data['discounts']))
            $this->discountAction->set($model, $data['discounts']);
    }

    public function createModel(array $data): Model
    {
        $data["creator_id"] = auth('api')->user()->id;
        $model = $this->model->create($this->processPlan($data));
        if (isset($data['item_ids']))
            $model->items()->attach($data['item_ids']);
        $this->relationsProcess($model,$data);
        return $this->getModel($model->id);
    }

    public function updateModel($id, array $data): Model
    {
        $model = $this->model->find($id);
        if(!$model)
            throw new NotFoundHttpException("No plans found with id $id");
        $model->update($this->processPlan($data));
        if (isset($data['item_ids']))
            $model->items()->sync($data['item_ids']);
        $this->relationsProcess($model,$data);
        return $this->getModel($model->id, ['items']);
    }

    /**
     * @return mixed
     */
    public function list(): mixed
    {
        return DietPlan::with($this->relations)->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function delete($id)
    {
        $plan = $this->model->find($id);
        $plan->items()->detach();
        return $plan->delete();
    }

    public function getPlan(int $id){
        return $this->getModel($id, ['items.locales', 'items.media']);
    }
}
