<?php


namespace App\Actions;


use App\Models\Category;
use App\Models\Media;
use App\Models\Menu;
use App\Models\User;
use App\Repository\Eloquent\CategoryRepository;
use App\Repository\Eloquent\ItemRepository;
use App\Repository\Eloquent\LocaleRepository;
use App\Repository\Eloquent\MediaRepository;
use DB;
use Illuminate\Database\Eloquent\Model;
use Storage;

class MediaAction
{

    public function __construct(private MediaRepository $repository,
                                private LocaleRepository $localeRepository)
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
                "user_id" => auth('sanctum')->user()->id];
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
        if(get_class($model) === Category::class ||
            get_class($model) === User::class)
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
        $directory = storage_path('app/' . $savePath);
        // TODO :: fix this in both servers
        try{
            if (!is_dir($directory))
                mkdir($directory, 0777, true);
            else
                chmod($directory, 0777);
        }catch (\Exception $exception){
            \Log::error($exception->getMessage());
        }
        Storage::putFileAs($savePath, $file, $file_name);
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

    /**
     * To make a fine name from file name
     * @param $name
     * @return string
     */
    private function fineName($name)
    {
        $name = str_ireplace('-', ' ', $name);
        $name = str_ireplace('_', ' ', $name);
        $name = str_ireplace('+', ' ', $name);
        return ucfirst($name);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function smartMenuUploader( $myFile,  $user)
    {
        $othersName = 'Others';
        $mediaAction = app(MediaAction::class);
        $splitNames = explode('/', $myFile['fullPath']);
        $item_name = array_pop($splitNames);
        $item_name = $this->fineName(explode('.', $item_name)[0]);
        $menu = Menu::find($user['menuId']);
        if (count($splitNames)) {
            $categories = app(CategoryRepository::class)
                ->createCategoriesFromPath(
                    $splitNames,
                    $myFile['uploadedFilePath'],
                    $user['userId'],
                    $user['menuId']
                );
            $current_categories = $categories->all();
            $savingCategory = end($current_categories);
        } else {
            // TODO :: first user locale

            $savingCategory = Category::where([
                "menu_id" => $user['menuId']
            ])->whereHas('locales', function ($q) use($othersName){
                $q->where('name',$othersName);
            })->first();
            if(!$savingCategory){
                // TODO :: Pass first user locale , from user locales setting
                $savingCategory = app(CategoryRepository::class)->createModel([
                    "locales" => [
                        ["name" => $othersName, 'locale' => 'ar'],
                        ["name" => $othersName, 'locale' => 'en']
                    ],
                    "image" => $myFile['uploadedFilePath'],
                    "user_id" => $user['userId'],
                    "business_id" => $menu->business_id,
                    "menu_id" => $user['menuId']
                ]);
            }

        }
        // TODO :: Pass first user locale , from user locales setting
        $item = app(ItemRepository::class)->create([
            'locales' => [
                ['name' => $item_name, 'locale' => 'en'],
                ['name' => $item_name, 'locale' => 'ar'],
            ],
            'category_id' => ($savingCategory->id),
            'user_id' => $user['userId'],
            "business_id" => $menu->business_id
        ]);
        $categoryImages = Category::with('media')->find($savingCategory->id)->media;
        if (count($categoryImages) === 0) {
            $mediaAction->storeMedia($myFile['uploadedFilePath'], $myFile['fileType'], $item_name, $savingCategory);
        }
        $mediaAction->storeMedia($myFile['uploadedFilePath'], $myFile['fileType'], $item_name, $item);
    }

    public function sort($data)
    {
        DB::transaction(function () use ($data) {
            foreach ($data['sortedIds'] as $index => $id) {
                Media::where('id', $id)->update(['sort' => $index + 1]);
            }
        });
        return true;
    }


}
