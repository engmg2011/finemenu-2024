<?php


namespace App\Repository;


use Illuminate\Database\Eloquent\Model;

interface LocaleRepositoryInterface
{

    public function processLocale(array $data);

    public function createLocale(Model $model, array $locales);

    public function updateLocale($id, array $data);

    public function setLocales($model, $locales);

    public function deleteEntityLocales($model);
}
