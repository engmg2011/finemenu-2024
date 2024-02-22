<?php

namespace App\Repository\Eloquent;

use App\Actions\DiscountAction;
use App\Actions\LocaleAction;
use App\Actions\MediaAction;
use App\Actions\PriceAction;
use App\Models\Plan;
use App\Repository\PlanRepositoryInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class PlanRepository extends BaseRepository implements PlanRepositoryInterface
{
    public array $relations = ['locales', 'prices', 'media', 'discounts'];

    /**
     * UserRepository constructor.
     * @param Plan $model
     * @param MediaAction $mediaAction
     * @param LocaleAction $localeAction
     * @param PriceAction $priceAction
     * @param DiscountAction $discountAction
     */
    public function __construct(Plan                            $model,
                                private readonly MediaAction    $mediaAction,
                                private readonly LocaleAction   $localeAction,
                                private readonly PriceAction    $priceAction,
                                private readonly DiscountAction $discountAction)
    {
        parent::__construct($model);
    }

    /**
     * @param int $id
     * @return Builder|Plan
     */
    public function getModel(int $id): Builder|Plan
    {
        return Plan::with($this->relations)->find($id);
    }

    public function processPlan(array $data): array
    {
        $data['user_id'] = auth('api')->user()->id;
        return array_only($data, ['restaurant_id', 'category_id', 'user_id']);
    }

    public function createModel(array $data): Model
    {
        $data["creator_id"] = auth('api')->user()->id;
        $model = $this->model->create($this->processPlan($data));
        if (isset($data['locales']))
            $this->localeAction->createLocale($model, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        if (isset($data['prices']))
            $this->priceAction->setPrices($model, $data['prices']);
        if (isset($data['discounts']))
            $this->discountAction->set($model, $data['discounts']);
        return $this->getModel($model->id);
    }

    public function updateModel($id, array $data): Model
    {
        $model = tap($this->model->find($id))
            ->update($this->processPlan($data));
        if (isset($model['locales']))
            $this->localeAction->updateLocales($model, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        if (isset($data['prices']))
            $this->priceAction->setPrices($model, $data['prices']);
        if (isset($data['discounts']))
            $this->discountAction->set($model, $data['discounts']);
        return $model;
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return Plan::with($this->relations)->orderByDesc('id')->paginate(request('per-page', 15));
    }


}
