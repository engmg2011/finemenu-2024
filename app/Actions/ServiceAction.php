<?php


namespace App\Actions;

use App\Models\Service;
use App\Repository\Eloquent\LocaleRepository;
use App\Repository\Eloquent\PriceRepository;
use App\Repository\Eloquent\ServiceRepository;
use Illuminate\Database\Eloquent\Model;

class ServiceAction
{
    public $modelRelations = ["locales", "media" , "prices.locales"];
    public function __construct(private ServiceRepository $repository,
                                private MediaAction $mediaAction,
                                private LocaleRepository $localeAction,
                                private PriceRepository $priceRepository,){
    }

    public function process(array $data): array
    {
        if (strpos($data['serviceable_type'], 'App') === false)
            $data['serviceable_type'] = 'App\\Models\\' . $data['serviceable_type'];
        $data['user_id'] = auth('sanctum')->user()->id;
        return array_only($data, ['user_id', 'serviceable_id', 'serviceable_type']);
    }

    public function create(array $data)
    {
        $model = $this->repository->create($this->process($data));
        $this->relations($model,$data);
        return $this->get($model->id);
    }

    public function update($id, array $data): Model
    {
        $model = tap($this->repository->find($id))
            ->update($this->process($data));
        $this->relations($model,$data);
        return $this->get($model->id);
    }

    public function relations(&$model, &$data)
    {
        if (isset($data['locales']))
            $this->localeAction->setLocales($model, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        if (isset($data['prices']))
            $this->priceRepository->setPrices($model, $data['prices']);
    }


    /**
     * @return mixed
     */
    public function list()
    {
        return Service::with($this->modelRelations)->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function get(int $id)
    {
        return Service::with($this->modelRelations)->find($id);
    }

    public function destroy($id): ?bool
    {
        $this->localeAction->deleteEntityLocales(Service::find($id));
        return $this->repository->delete($id);
    }

}
