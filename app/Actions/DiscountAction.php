<?php


namespace App\Actions;

use App\Models\Discount;
use App\Repository\Eloquent\DiscountRepository;
use App\Repository\Eloquent\LocaleRepository;
use Illuminate\Database\Eloquent\Model;

class DiscountAction
{
    public function __construct(private DiscountRepository $repository,
                                private LocaleRepository   $localeRespository)
    {
    }

    public function process(array $data): array
    {
        return array_only($data, ['amount', 'type', 'from', 'to', 'discountable_id', 'discountable_type', 'user_id']);
    }

    public function create(array $data): Model
    {
        $model = $this->repository->create($this->process($data));
        if (isset($data['locales']))
            $this->localeRespository->createLocale($model, $data['locales']);
        return $model;
    }

    public function update($id, array $data): Model
    {
        $model = tap($this->repository->find($id))
            ->update($this->process($data));
        if (isset($data['locales']))
            $this->localeRespository->setLocales($model, $data['locales']);
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
            $discount['user_id'] = auth('api')->user()->id;
            if (isset($discount['id']) && $discount['id'])
                $this->update($discount['id'], $discount);
            else
                $this->create($discount);
        }
    }

    public function destroy($id): ?bool
    {
        $this->localeRespository->deleteEntityLocales(Discount::find($id));
        return $this->repository->delete($id);
    }

    /**
     * @param $model
     * @param $data: Array of discount ids [1,2]
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

}
