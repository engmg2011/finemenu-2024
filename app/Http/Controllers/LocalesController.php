<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Repository\LocaleRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class LocalesController extends Controller
{
    public function __construct(public LocaleRepositoryInterface $repository)
    {

    }

    public function createModel(Request $request): Model
    {
        $data = $request->all();
        $model = Item::first();
        $data['localizable_id'] = $model->id;
        $data ['localizable_type'] = get_class($model);
        return $this->repository->create($data);
    }


}
