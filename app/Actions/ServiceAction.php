<?php


namespace App\Actions;

use App\Models\Service;
use App\Repository\Eloquent\LocaleRepository;
use App\Repository\Eloquent\ServiceRepository;
use Illuminate\Database\Eloquent\Model;

class ServiceAction
{
    public function __construct(private ServiceRepository $repository,
                                private MediaAction $mediaAction,
                                private LocaleRepository $localeAction){
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
        $this->localeAction->createLocale($model, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        return $model;
    }

    public function update($id, array $data): Model
    {
        $model = tap($this->repository->find($id))
            ->update($this->process($data));
        $this->localeAction->setLocales($model, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        return $model;
    }


    /**
     * @return mixed
     */
    public function list()
    {
        return Service::with('locales', 'media')->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function get(int $id)
    {
        return Service::with('locales')->find($id);
    }

    public function destroy($id): ?bool
    {
        $this->localeAction->deleteEntityLocales(Service::find($id));
        return $this->repository->delete($id);
    }

}
