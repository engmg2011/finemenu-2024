<?php


namespace App\Actions;


use App\Models\Price;
use App\Repository\Eloquent\PriceRepository;
use Illuminate\Database\Eloquent\Model;

class PriceAction
{

    public function __construct(private PriceRepository $repository, private LocaleAction $localeAction)
    {
    }

    public function process(array $data): array
    {
        return array_only($data, ['user_id', 'price', 'priceable_id', 'priceable_type']);
    }

    public function create(array $data)
    {
        $model = $this->repository->create($this->process($data));
        app(LocaleAction::class)->createLocale($model, $data['locales']);
        return $model;
    }

    public function update($id, array $data): Model
    {
        $model = tap($this->repository->find($id))
            ->update($this->process($data));
        app(LocaleAction::class)->updateLocales($model, $data['locales']);
        return $model;
    }

    public function setPrices($model, &$prices)
    {
        foreach ($prices as &$price) {
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
        $this->localeAction->deleteEntityLocales(Price::find($id));
        return $this->repository->delete($id);
    }
}
