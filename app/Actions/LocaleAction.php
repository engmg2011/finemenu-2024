<?php


namespace App\Actions;


use App\Repository\Eloquent\LocaleRepository;
use Illuminate\Database\Eloquent\Model;

class LocaleAction
{
    private $repository;

    public function __construct(LocaleRepository $repository)
    {
        $this->repository = $repository;
    }

    public function processLocale(array $data)
    {
        return array_only($data, ['name', 'description', 'locale', 'localizable_type', 'localizable_id']);
    }

    /**
     * Creates single or multiple locales
     * @param Model|null $model
     * @param array $locales
     * @return \phpDocumentor\Reflection\Types\Boolean
     */
    public function createLocale(Model $model = null, array $locales)
    {
        $dataLocales = [];
        if ($model && count($locales) > 0) {
            foreach ($locales as $singleLocale) {
                $singleLocale += [
                    'localizable_id' => $model->id,
                    'localizable_type' => get_class($model),
                    'locale' => $singleLocale['locale'] ?? \App::getLocale()
                ];
                $dataLocales[] = $this->processLocale($singleLocale);
            }
        } else
            $dataLocales = $this->processLocale($locales);
        return $this->repository->insert($dataLocales);
    }

    public function updateLocale($id, array $data)
    {
        return $this->repository->find($id)->update($this->processLocale($data));
    }

    /**
     * @param $model // The localizable model
     * @param $locales // The locales data
     */
    public function updateLocales($model, $locales)
    {
        foreach ($locales as &$locale) {
            if (!isset($locale['id']))
                $this->createLocale($model, [$locale]);
            else
                // Be noted that locale id sent in locale not model
                $this->updateLocale($locale['id'], [
                    "name" => $locale['name'],
                    "description" => $locale['description'] ?? '',
                    "locale" => $locale['locale'] ?? \App::getLocale(),
                    "localizable_type" => get_class($model),
                    "localizable_id" => $model->id
                ]);
        }
    }

    public function deleteEntityLocales($model)
    {
        $this->repository->where(['localizable_type' => get_class($model), 'localizable_id' => $model->id])->delete();
    }

}
