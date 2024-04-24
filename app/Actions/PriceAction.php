<?php


namespace App\Actions;


use App\Models\Price;
use App\Repository\Eloquent\LocaleRepository;
use App\Repository\Eloquent\PriceRepository;
use Illuminate\Database\Eloquent\Model;

class PriceAction
{

    public function __construct(private PriceRepository $repository, private LocaleRepository $localeRepository)
    {
    }

    public function process(array $data): array
    {
        return array_only($data, ['user_id', 'price', 'priceable_id', 'priceable_type']);
    }

    public function create(array $data)
    {
        $model = $this->repository->create($this->process($data));
        if(isset($data['locales']))
            $this->localeRepository->setLocales($model, $data['locales']);
        return $model;
    }

    public function update($id, $data): Model
    {
        $model = tap($this->repository->find($id))
            ->update($this->process($data));
        $this->localeRepository->setLocales($model, $data['locales']);
        return $model;
    }

    public function setPrices(&$model, $prices)
    {
        foreach ($prices as &$price) {
            $price['priceable_id'] = $model['id'];
            $price['priceable_type'] = get_class($model);
            $price['user_id'] = auth('api')->user()->id;
            if (isset($priceData['id']) && $priceData['id'])
                $this->update($priceData['id'], $priceData);
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
        return $this->repository->delete($id);
    }
}
