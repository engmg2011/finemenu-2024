<?php


namespace App\Actions;


use App\Jobs\UploadMenuQueue;
use App\Models\Category;
use App\Models\Media;
use App\Repository\Eloquent\LocaleRepository;
use App\Repository\Eloquent\MediaRepository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Storage;

class MediaAction
{

    public function __construct(private MediaRepository $repository, private LocaleRepository $localeRepository)
    {
    }

    public function process(array $data): array
    {
        return array_only($data, ['user_id', 'src', 'type', 'mediable_id', 'mediable_type', 'slug']);
    }

    public static function relationMediaFields($mediable): array
    {
        return [
                "mediable_type" => get_class($mediable),
                "mediable_id" => $mediable->id,
                "user_id" => auth('api')->user()->id];
    }

    public function create(array $data)
    {
        $model = $this->repository->create($this->process($data));
        $this->localeRepository->setLocales($model, $data['locales']);
        if (isset($data['image']))
            $this->addMedia($model, $data);
        return $model;
    }

    public function update($id, array $data): Model
    {
        $model = tap($this->repository->find($id))
            ->update($this->process($data));
        $this->localeRepository->setLocales($model, $data['locales']);
//        if (isset($data['image_id']) && isset($data['image']))
//            $this->updateMedia($model, $data);
        return $model;
    }

    public function addMedia($model, $data)
    {
        $this->storeMedia($data['image'], 'image', $data['name'] ?? '', $model);
    }

    public function setMedia(&$model, &$data)
    {
        if(get_class($model) === Category::class)
            $this->removeUnsentMedia($model, $data);
        foreach ($data as &$media)
            $this->updateMediaMulti($media, $model);
    }

    public function removeUnsentMedia(&$model, &$data)
    {
        // Get current assigned media
        $mediaList = Media::where(['mediable_id' => $model->id, 'mediable_type' => get_class($model)])->pluck('id')->toArray();
        // Compare with sent ids
        $sentList = [];
        foreach ($data as &$media) {
            if (isset($media['id']))
                $sentList[] = $media['id'];
        }
        // remove difference
        $difference = array_diff($mediaList, $sentList);
        Media::whereIn('id', $difference)->delete();
    }

    public function updateMediaMulti(&$data, &$mediable)
    {
        $mediaSrc = parse_url($data['src'])['path'];
        $processedData = $this->process($data + ['src' => $mediaSrc, 'type' => 'image']
            + MediaAction::relationMediaFields($mediable));
        $this->repository->updateOrCreate(
            ['id' => $data['id'] ?? null],
            $processedData
        );
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return Media::with('locales')->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function get(int $id)
    {
        return Media::with('locales')->find($id);
    }

    public function uploadMedia($file, $file_name, $savePath)
    {
        Storage::putFileAs($savePath, $file->path(), $file_name);
        return $savePath . $file_name;
    }

    public function storeMedia($src, $type, $name, $mediable)
    {
        $src = str_replace('http://localhost/', '', $src);
        $src = str_replace('http://localhost:8000/', '', $src);
        $media = app(MediaRepository::class)->create([
                "src" => $src,
                "type" => $type
            ] + MediaAction::relationMediaFields($mediable));
        if ($name)
            app(LocaleRepository::class)->create([
                "name" => $name,
                "locale" => "en",
                "localizable_type" => Media::class,
                "localizable_id" => $media->id
            ]);
        return Media::with('locales')->find($media->id);
    }

    public function delete(int $id){
        $this->localeRepository->deleteEntityLocales(Media::find($id));
        return app(MediaRepository::class)->delete($id);
    }

}
