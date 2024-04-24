<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Repository\Eloquent\LocaleRepository;
use Illuminate\Http\Request;

class LocalesController extends Controller
{
    public function __construct(public LocaleRepository $repository)
    {
    }

    public function createModel(Request $request)
    {
        $data = $request->all();
        $model = Item::first();
        $data['localizable_id'] = $model->id;
        $data ['localizable_type'] = get_class($model);
        return response()->json($this->repository->create($data));
    }

    public function delete($id)
    {
        return response()->json($this->repository->delete($id));
    }

}
