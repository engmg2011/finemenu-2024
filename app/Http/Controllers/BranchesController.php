<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Models\Branch;
use App\Repository\BranchRepositoryInterface;
use App\Services\QrService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\File;

class BranchesController extends Controller
{
    public function __construct(private BranchRepositoryInterface $repository)
    {
    }

    /**
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function index($businessId)
    {
        return DataResource::collection($this->repository->listWhere(
            ['business_id' => $businessId],
            ['locales'])
        );
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function createModel(Request $request, $businessId)
    {
        return response()->json($this->repository->createModel($businessId, $request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($businessId, $id)
    {
        return response()->json($this->repository->get($businessId, $id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param $businessId
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $businessId, $id)
    {
        return response()->json($this->repository->updateModel($businessId, $id, $request->all()));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($businessId, $id)
    {
        return response()->json($this->repository->destroy($businessId, $id));
    }

    public function sort(Request $request,$businessId)
    {
        return response()->json($this->repository->sort($businessId, $request->all()));
    }


    public function referenceQr(){
        $branchId = \request()->route('modelId');
        $resolution = \request('resolution', '');
        $resolutionValue = $resolution === 'hd' ? 1024 : 250;
        $branch = Branch::find($branchId);
        $imageName = $branch->slug.($resolution === 'hd' ? '_HD' : '').'.svg';
        $content = env('WEB_APP_URL') . '/'.$branch->slug;
        if(\request()->has('download')){
            $qrImage = (new QrService())->generateBranchQrCode($branch, $content, $resolutionValue);
            return response()->streamDownload(static function () use ($qrImage) {
                echo $qrImage;
            }, $imageName);
        }
        if(\request()->has('preview')){
            $qrImage = (new QrService())->generateBranchQrCode($branch, $content, $resolutionValue);
            return response()->make($qrImage);
        }

       return (new QrService())->generateBase64QrCode($content);
    }

    public function PreviewQR($branchId) {
        $branch = Branch::find($branchId);
        $branch->fill(\request()->all());
        $branch->setting->fill(\request()->all());
        $filePath = null;
        if(\request()->hasFile('qr_logo_image')){
            $file = \request()->file('qr_logo_image');
            $name = date('D-M-Y') . '-' . mt_rand() . '-QR' . '.png';
            $filePath = sys_get_temp_dir().'/'.$name;
            $file->move(sys_get_temp_dir(), $name);
        }
        $content = url('/web-app/business/'.$branch->slug);
        $qrImage = (new QrService())->generateBranchQrCode($branch, $content, 250, 0.3, $filePath);
        if(!is_null($filePath))
            File::delete($filePath);
        return base64_encode($qrImage);
    }


}
