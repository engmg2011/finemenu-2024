<?php


namespace App\Actions;


use App\Jobs\UploadMenuQueue;
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
//        $this->removeUnsentMedia($model, $data);
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

    public function postUpload(Request $request)
    {
        $user_id = auth('api')->user()->id;
        request()->validate(['file' => 'required|file']);
        $file = $request->file('file');
        $file_type = $file->getMimeType();

        $file_name = rand(1000, 10000) . '_' . $file->getClientOriginalName();
        $savePath = $user_id . '/';
        $uploadedFile = $savePath . $file_name;
        $storagePath = "storage/" . $uploadedFile;
        if ($request->headers->has('convert-item')) {
            $fullPath = $request->header('full-path');
            $myFile = [
                'fullPath' => $fullPath,
                'uploadedFilePath' => $storagePath,
                'fileType' => $file_type
            ];
            $user = [
                'userId' => auth('api')->user()->id,
                'restaurantId' => request()->header('restaurant-id'),
                'menuId' => request()->header('menu-id'),
                'locale' => request()->header('locale')
            ];
            UploadMenuQueue::dispatch( $myFile,$user);
        }

        $this->uploadMedia($file, $file_name, "public/" . $savePath);

        return response()->json([
            'file' => $storagePath,
            'item' => $item ?? null,
            'media' => $media ?? null
        ]);
    }

}
