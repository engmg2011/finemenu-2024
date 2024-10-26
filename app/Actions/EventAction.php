<?php


namespace App\Actions;

use App\Models\Event;
use App\Repository\Eloquent\EventRepository;
use App\Repository\Eloquent\LocaleRepository;
use Illuminate\Database\Eloquent\Model;

class EventAction
{

    public function __construct(private EventRepository $repository,
                                private MediaAction $mediaAction,
                                private LocaleRepository $localeRepository)
    {
    }

    public function process(array $data): array
    {
        return array_only($data, ['user_id', 'start', 'end', 'eventable_id', 'eventable_type']);
    }

    public function create(array $data)
    {
        $data['user_id'] = auth('sanctum')->user()->id;
        $model = $this->repository->create($this->process($data));
        $this->localeRepository->createLocale($model, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        return $model;
    }

    public function update($id, array $data): Model
    {
        $model = tap($this->repository->find($id))
            ->update($this->process($data));
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
        return $this->repository->list();
    }

    public function get(int $id)
    {
        return Event::with(['locales', 'media'])->find($id);
    }

    public function destroy($id): ?bool
    {
        $this->localeRepository->deleteEntityLocales(Event::find($id));
        return $this->repository->delete($id);
    }
}
