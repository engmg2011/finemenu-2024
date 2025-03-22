<?php

namespace App\Repository\Eloquent;


use App\Models\Discount;
use App\Repository\DiscountRepositoryInteface;
use App\Repository\LocaleRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class DiscountRepository extends BaseRepository implements DiscountRepositoryInteface
{

    public function __construct(Discount                                   $model,
                                private readonly LocaleRepositoryInterface $localeRepository)
    {
        parent::__construct($model);
    }


    public function process(array $data): array
    {
        return array_only($data, ['amount', 'type', 'from', 'to', 'discountable_id', 'discountable_type', 'user_id']);
    }

    public function createModel(array $data): Model
    {
        $model = $this->create($this->process($data));
        if (isset($data['locales']))
            $this->localeRepository->createLocale($model, $data['locales']);
        return $model;
    }

    public function updateModel($id, array $data): Model
    {
        $model = tap($this->find($id))
            ->update($this->process($data));
        if (isset($data['locales']))
            $this->localeRepository->setLocales($model, $data['locales']);
        return $model;
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return Discount::with(['locales'])->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function get(int $id)
    {
        return Discount::with(['locales'])->find($id);
    }

    public function set($model, &$discounts)
    {
        foreach ($discounts as &$discount) {
            $discount['discountable_id'] = $model['id'];
            $discount['discountable_type'] = get_class($model);
            $discount['user_id'] = auth('sanctum')->user()->id;
            if (isset($discount['id']) && $discount['id'])
                $this->updateModel($discount['id'], $discount);
            else
                $this->createModel($discount);
        }
    }

    public function destroy($id): ?bool
    {
        $this->localeRepository->deleteEntityLocales(Discount::find($id));
        return $this->delete($id);
    }

    /**
     * @param $model
     * @param $data : Array of discount ids [1,2]
     * @return void
     */
    public function setModelDiscounts(&$model, &$data)
    {
        $discounts = Discount::with('locales')->whereIn('id', $data)
            ->select('id', 'amount', 'type', 'from', 'to')->get()?->toArray();
        foreach ($discounts as &$discount) {
            $discount['id'] = null;
            if (isset($discount['locales']))
                foreach ($discount['locales'] as &$locale) $locale['id'] = null;
        }
        $this->set($model, $discounts);
    }

    /**
     * @param $model
     * @param $data : Array of discount ids [1,2]
     * @return mixed[]|null
     */
    public function getModelDiscounts(&$model, &$data)
    {
        $discounts = Discount::with('locales')->whereIn('id', $data)
            ->select('id', 'amount', 'type', 'from', 'to')->get()?->toArray();
        foreach ($discounts as &$discount) {
            unset($discount['id']);
            if (isset($discount['locales']))
                foreach ($discount['locales'] as &$locale) $locale['id'] = null;
        }
        return $discounts;
    }

}
