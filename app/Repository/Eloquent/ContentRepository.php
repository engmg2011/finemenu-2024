<?php

namespace App\Repository\Eloquent;


use App\Models\Content;
use App\Repository\ContentRepositoryInterface;

class ContentRepository extends BaseRepository implements ContentRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param Content $model
     */
    public function __construct(Content $model) {
        parent::__construct($model);
    }

    public function list()
    {
        return $this->model::with(['locales'])->orderByDesc('id')->paginate(request('per-page', 15));
    }

}
