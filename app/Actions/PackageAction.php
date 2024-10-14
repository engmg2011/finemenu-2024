<?php


namespace App\Actions;

use App\Models\Package;
use App\Repository\Eloquent\LocaleRepository;
use App\Repository\Eloquent\PackageRepository;
use Illuminate\Database\Eloquent\Model;

class PackageAction
{
    public function __construct(private PackageRepository $repository,
                                private LocaleRepository $localeAction){
    }

    public function process(array $data): array
    {
        return array_only($data, ['days', 'type']);
    }

    public function create(array $data)
    {
        $model = $this->repository->create($this->process($data));
        $this->localeAction->createLocale($model, $data['locales']);
        return $model;
    }

    public function update($id, array $data): Model
    {
        $model = tap($this->repository->find($id))
            ->update($this->process($data));
        $this->localeAction->setLocales($model, $data['locales']);
        return $model;
    }


    /**
     * @return mixed
     */
    public function list()
    {
        return Package::with('locales')->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function get(int $id)
    {
        return Package::with('locales')->find($id);
    }

    public function destroy($id): ?bool
    {
        $this->localeAction->deleteEntityLocales(Package::find($id));
        return $this->repository->delete($id);
    }

}
