<?php


namespace App\Actions;

use App\Models\Hotel;
use App\Repository\Eloquent\HotelRepository;
use App\Repository\Eloquent\LocaleRepository;
use Illuminate\Database\Eloquent\Model;

class HotelAction
{
    private $repository, $mediaAction;

    public function __construct(HotelRepository $repository, MediaAction $mediaAction, private LocaleRepository $localeAction)
    {
        $this->repository = $repository;
        $this->mediaAction = $mediaAction;
    }

    public function processHotel(array $data): array
    {
        return array_only($data, ['name', 'user_id', 'creator_id', 'passcode', 'slug']);
    }

    public function createModel(array $data): Model
    {
        $data["creator_id"] = auth('api')->user()->id;
        $model = $this->repository->create($this->processHotel($data));
        if (isset($data['locales']))
            $this->localeAction->createLocale($model, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        return $model;
    }

    public function updateModel($id, array $data): Model
    {
        $model = tap($this->repository->find($id))
            ->update($this->processHotel($data));
        if (isset($data['locales']))
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
        return Hotel::with('locales','media')
            ->where('user_id', auth('api')->id())
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function getModel(int $id)
    {
        return Hotel::with('locales','media')->find($id);
    }

    public function destroy(int $id)
    {
        return Hotel::find($id)->delete();
    }
}
