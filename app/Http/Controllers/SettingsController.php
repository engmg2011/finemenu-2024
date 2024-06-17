<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Repository\SettingRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Exception;

class SettingsController extends Controller
{
    public function __construct(private SettingRepositoryInterface $repository)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        return DataResource::collection($this->repository->list());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    /*
     * [
    {
        "day": "Sat",
        "shifts": [
            {
                "to": "01:00",
                "from": "00:00"
            }
        ]
    },
    {
        "day": "Sun",
        "shifts": [
            {
                "to": "01:00",
                "from": "00:00"
            }
        ]
    }
]
SELECT settings.id , JSON_EXTRACT(settings.data ,"$[*].day") day,
	JSON_EXTRACT(settings.data ,"$[0].shifts[0].from") sfrom,
	JSON_EXTRACT(settings.data ,"$[0].shifts[0].to") sto
	FROM `settings` WHERE settings.key like "shifts"
 AND '10:30' BETWEEN TIME(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$[0].shifts[0].from'))) AND TIME(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$[0].shifts[0].to')));

    //////

SELECT
    CASE
        WHEN CURRENT_TIME() BETWEEN TIME(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$[0].shifts[0].from'))) AND TIME(JSON_UNQUOTE(JSON_EXTRACT(`data`, '$[0].shifts[0].to'))) THEN 'Open'
        ELSE 'Closed'
    END AS status
FROM
    settings  WHERE settings.key like "shifts";

     */
    public function createSetting(Request $request, int $modelId): JsonResponse
    {
        $modelName = $request->get('model');
        $model = app($modelName)->find($modelId);
        if (!$model)
            throw new Exception("no data found");
        return \response()->json($this->repository->createSetting($model, $request->all()));
    }

    public function setSetting(Request $request, int $modelId): JsonResponse
    {
        $modelName = $request->get('model');
        $model = app($modelName)->find($modelId);
        if (!$model)
            throw new Exception("no data found");
        return \response()->json(
            $this->repository->setSettings($model, $request->all())
        );
    }

    /**
     * @param Request $request
     * @param $modelId
     * @return JsonResponse
     */
    public function listSettings(Request $request, $modelId): JsonResponse
    {
        $modelName = $request->get('model');
        $model = app($modelName)->find($modelId);
        if (!$model)
            throw new \Exception("no data found");
        return \response()->json($this->repository->listSettings($model));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id): JsonResponse
    {
        return \response()->json($this->repository->find($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function updateSetting(Request $request, $modelId, $settingId)
    {
        $modelName = $request->get('model');
        $model = app($modelName)->find($modelId);
        $request->request->add(['id' => $settingId]);
        if (!$model)
            throw new Exception("no data found");
        return \response()->json($this->repository->updateSetting($model, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     */
    public function destroy($id)
    {
        return \response()->json($this->repository->delete($id));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param Request $request
     * @param $modelId
     * @param $settingId
     * @return JsonResponse
     */
    public function deleteSetting($modelId, $settingId): JsonResponse
    {
        $modelName = \request()->get('model');
        $relationModel = app($modelName)->find($modelId);
        if (!$relationModel)
            throw new Exception("no data found");
        \request()->request->add(['id' => $settingId]);
        return \response()->json($this->repository->deleteSetting($relationModel, \request()->all()));
    }
}
