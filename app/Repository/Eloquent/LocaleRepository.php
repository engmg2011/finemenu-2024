<?php

namespace App\Repository\Eloquent;


use App;
use App\Models\Locales;
use App\Repository\LocaleRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class LocaleRepository extends BaseRepository implements LocaleRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Locales $model
     */
    public function __construct(Locales $model) {
        parent::__construct($model);
    }

    public function processLocale(array $data)
    {
        return array_only($data, ['name', 'description', 'locale', 'localizable_type', 'localizable_id']);
    }

    /**
     * Creates single or multiple locales
     * @param Model|null $model
     * @param array $locales
     * @return Boolean
     */
    public function createLocale(Model $model = null, array $locales)
    {
        $dataLocales = [];
        if ($model && count($locales) > 0) {
            foreach ($locales as $singleLocale) {
                $singleLocale += [
                    'localizable_id' => $model->id,
                    'localizable_type' => get_class($model),
                    'locale' => $singleLocale['locale'] ?? App::getLocale()
                ];
                $dataLocales[] = $this->processLocale($singleLocale);
            }
        } else
            $dataLocales = $this->processLocale($locales);
        return $this->model->insert($dataLocales);
    }

    public function updateLocale($id, array $data)
    {
        return $this->model->find($id)->update($this->processLocale($data));
    }

    /**
     * @param $model // The localizable model
     * @param $locales // The locales data
     */
    public function setLocales($model, $locales)
    {
        foreach ($locales as &$locale) {
            if (!isset($locale['id']))
                $this->createLocale($model, [$locale]);
            else
                // Be noted that locale id sent in locale not model
                $this->updateLocale($locale['id'], [
                    "name" => $locale['name'],
                    "description" => $locale['description'] ?? '',
                    "locale" => $locale['locale'] ?? App::getLocale(),
                    "localizable_type" => get_class($model),
                    "localizable_id" => $model->id
                ]);
        }
    }

    public function deleteEntityLocales($model)
    {
        $this->model->where(['localizable_type' => get_class($model), 'localizable_id' => $model->id])->delete();
    }
}
