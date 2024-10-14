<?php


namespace App\Actions;

use App\Models\Addon;
use App\Repository\Eloquent\AddonRepository;
use App\Repository\Eloquent\LocaleRepository;
use App\Repository\Eloquent\PriceRepository;
use Illuminate\Database\Eloquent\Model;

class AddonAction
{
    public function __construct(private AddonRepository  $repository,
                                private MediaAction      $mediaAction,
                                private LocaleRepository $localeRepository,
                                private PriceRepository      $priceAction){
    }

    public function process(array $data): array
    {
        return array_only($data, ['addonable_id' ,'addonable_type', 'price', 'user_id', 'parent_id']);
    }

    public function create(array $data): Model
    {
        $model = $this->repository->create($this->process($data));
        if (isset($data['locales']))
            $this->localeRepository->createLocale($model, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        if (isset($data['prices']))
            $this->priceAction->setPrices($model, $data['prices']);

        return $model;
    }

    public function update($id, array $data): Model
    {
        $model = tap($this->repository->find($id))
            ->update($this->process($data));
        if (isset($data['locales']))
            $this->localeRepository->setLocales($model, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        return $model;
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return Addon::with(['locales','media'])->orderByDesc('id')->paginate(request('per-page' , 15));
    }

    public function getModel(int $id)
    {
        return Addon::with(['locales','media'])->find($id);
    }

    public function set($model, &$addons)
    {
        foreach ($addons as &$addon) {
            $addon['addonable_id'] = $model['id'];
            $addon['addonable_type'] = get_class($model);
            $addon['user_id'] = auth('api')->user()->id;
            $addon['parent_id'] = $model['parent_id'];
            if (isset($addon['id']) && $addon['id'])
                $this->update($addon['id'], $addon);
            else
                $this->create($addon);
        }
    }


    public function destroy($id): ?bool
    {
        $this->localeRepository->deleteEntityLocales(Addon::find($id));
        return $this->repository->delete($id);
    }

}
