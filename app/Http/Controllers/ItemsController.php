<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Models\Branch;
use App\Models\Business;
use App\Repository\ItemRepositoryInterface;
use App\Services\QrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use function response;

class ItemsController extends Controller
{
    public function __construct(private ItemRepositoryInterface $repository)
    {
    }
    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index()
    {
        $branchId = request()->get('branchId') ?? request()->route()->parameter('branchId');
        $businessId = request()->route('businessId');
        return DataResource::collection($this->repository->listModel($businessId, $branchId));
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function search()
    {
        $branchId = request()->get('branchId') ?? request()->route()->parameter('branchId');
        $businessId = request()->route('businessId');
        return DataResource::collection($this->repository->search($businessId, $branchId));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request, $businessId)
    {
        $data = $request->all();
        $this->setUtcDates($data, $businessId);
        if (isset($data['similar_ids']) && is_array($data['similar_ids']) && count($data['similar_ids']) > 0)
            $this->repository->validateSimilarItems($data['menu_id']);
        return response()->json($this->repository->create($data));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($businessId, $id)
    {
        return response()->json($this->repository->get($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $businessId, $id)
    {
        $data = $request->all();
        $this->setUtcDates($data, $businessId);
        if (isset($data['similar_ids']) && is_array($data['similar_ids']) && count($data['similar_ids']) > 0) {
            $this->repository->validateSimilarItems($data['similar_ids']);
        }
        return response()->json($this->repository->updateModel($id, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($businessId, $id)
    {
        return response()->json($this->repository->destroy($id));
    }

    public function sort(Request $request)
    {
        return response()->json($this->repository->sort($request->all()));
    }

    public function listHolidays($businessId, $itemId)
    {
        return response()->json($this->repository->listHolidays($businessId, $itemId));

    }

    public function syncHolidays($businessId, $itemId)
    {
        return response()->json($this->repository->syncHolidays($businessId, $itemId));

    }

    public function setUtcDates(&$data, $businessId)
    {
        $business = Business::find($businessId);
        if (isset($data['discounts']) && is_array($data['discounts'])) {
            for ($i = 0; $i < count($data['discounts']); $i++) {
                $data['discounts'][$i]['from'] = businessToUtcConverter($data['discounts'][$i]['from'], $business, 'Y-m-d H:i:s');
                $data['discounts'][$i]['to'] = businessToUtcConverter($data['discounts'][$i]['to'], $business, 'Y-m-d H:i:s');
            }
        }
    }

    public function qrCode($businessId, $branchId, $itemId)
    {
        $branch = Branch::find($branchId);
        $content = env('WEB_APP_URL') . '/'.$branch->slug.'/items/'.$itemId ;
        $compare = request()->has('compare') && request('compare') === 'true';
        if($compare)
            $content .= "?compare=true";
        $imageName = "item_" . $itemId . '_QR'.($compare ? '_compare' : '').'.svg';
        if (\request()->has('download')) {
            $qrImage = (new QrService())->generateItemQrCode($content);
            return response()->streamDownload(static function () use ($qrImage) {
                echo $qrImage;
            }, $imageName);
        }
        if (\request()->has('preview')) {
            $qrImage = (new QrService())->generateItemQrCode($content);
            return response()->make($qrImage);
        }

        return (new QrService())->generateBase64QrCode($content);
    }
}
