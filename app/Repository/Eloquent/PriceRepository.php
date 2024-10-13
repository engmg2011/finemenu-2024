<?php

namespace App\Repository\Eloquent;



use App\Models\Price;
use App\Repository\PriceRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class PriceRepository extends BaseRepository implements PriceRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Price $model
     */
    public function __construct(Price $model, private LocaleRepository $localeRepository) {
        parent::__construct($model);
    }


    public function process(array $data): array
    {
        return array_only($data, ['user_id', 'price', 'priceable_id', 'priceable_type']);
    }

    public function create(array $data): Model
    {
        $model = $this->model->create($this->process($data));
        if(isset($data['locales']))
            $this->localeRepository->setLocales($model, $data['locales']);
        return $model;
    }

    public function update($id, $data): Model
    {
        $model = tap($this->model->find($id))
            ->update($this->process($data));
        $this->localeRepository->setLocales($model, $data['locales']);
        return $model;
    }

    public function setPrices(&$model, $prices, bool $create = false)
    {
        foreach ($prices as &$price) {
            if($create)
                $price['id'] = null;
            $price['priceable_id'] = $model['id'];
            $price['priceable_type'] = get_class($model);
            $price['user_id'] = auth('api')->user()->id;
            if (isset($price['id']) && $price['id'])
                $this->update($price['id'], $price);
            else
                $this->create($price);
        }
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return Price::with('locales')->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function get(int $id)
    {
        return Price::with('locales')->find($id);
    }

    public function destroy($id): ?bool
    {
        $this->localeRepository->deleteEntityLocales(Price::find($id));
        return $this->delete($id);
    }
}
