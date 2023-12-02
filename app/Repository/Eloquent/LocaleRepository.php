<?php

namespace App\Repository\Eloquent;


use App\Models\Locales;
use App\Repository\LocaleRepositoryInterface;

class LocaleRepository extends BaseRepository implements LocaleRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Locales $model
     */
    public function __construct(Locales $model) {
        parent::__construct($model);
    }

}
