<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Models\Business;
use App\Models\Locales;
use App\Models\Setting;
use App\Repository\AreaRepositoryInterface;
use App\Repository\BranchRepositoryInterface;
use App\Repository\BusinessRepositoryInterface;
use App\Repository\CategoryRepositoryInterface;
use App\Repository\InvoiceRepositoryInterface;
use App\Repository\ItemRepositoryInterface;
use App\Repository\MenuRepositoryInterface;
use App\Repository\ReservationRepositoryInterface;
use App\Repository\SeatRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use App\Services\AuditService;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Storage;

class BusinessController extends Controller
{
    public function __construct(private BusinessRepositoryInterface $repository)
    {
    }

    public function menu($businessId)
    {
        return response()->json($this->repository->getModel($businessId));
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
     * Display a listing of the resource.
     *
     * @return AnonymousResourceCollection
     */
    public function businessList()
    {
        return DataResource::collection($this->repository->businessList());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function create(Request $request)
    {
        return \response()->json($this->repository->createModel($request->all()));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show($id)
    {
        return \response()->json($this->repository->getModel($id));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        return \response()->json($this->repository->updateModel($id, $request->all() + [
                "user_id" => auth('sanctum')->user()->id]));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        return \response()->json($this->repository->destroy($id));
    }

    public function businessBackup($businessId)
    {
        $business = Business::find($businessId);
        $locales = Locales::where(['localizable_type' => Business::class,
            'localizable_id' => $businessId])->get();
        $settings = Setting::where('settable_type', Business::class)
            ->where('settable_id', $businessId)->get();
        return compact('business', 'locales', 'settings');
    }


    public function backup($businessId)
    {
        $businessBup = $this->businessBackup($businessId);
        $usersBup = app(UserRepositoryInterface::class)->backup($businessId);
        $branchesBup = app(BranchRepositoryInterface::class)->backup($businessId);
        $menusBup = app(MenuRepositoryInterface::class)->backup($businessId);
        $categoriesBup = app(CategoryRepositoryInterface::class)->backup($businessId);
        $categoryIds = $categoriesBup['categories']->pluck('id')->toArray();
        $itemsBup = app(ItemRepositoryInterface::class)->backup($categoryIds);
        $reservationsBup = app(ReservationRepositoryInterface::class)->backup($businessId);
        $invoicesBup = app(InvoiceRepositoryInterface::class)->backup($businessId);
        $auditBup = app(AuditService::class)->backup($businessId);
        $areasBup = app(AreaRepositoryInterface::class)->backup($businessId);
        $seatsBup = app(SeatRepositoryInterface::class)->backup($businessId);

        $data = [
            'business' => $businessBup,
            'users' => $usersBup,
            'branches' => $branchesBup,
            'menus' => $menusBup,
            'categories' => $categoriesBup,
            'items' => $itemsBup,
            'reservations' => $reservationsBup,
            'invoices' => $invoicesBup,
            'audit' => $auditBup,
            'areas' => $areasBup,
            'seats' => $seatsBup,
        ];
        $encrypted = encrypt($data);
        $file = "backups/business_".$businessId."_".Carbon::now()->format("y-m-d_H:i").".backup";
        Storage::put($file, $encrypted);
        return json_encode(url($file));
    }

    public function restore(Request $request, $businessId)
    {
        $file = $request->file('file');
        $data = decrypt(file_get_contents($file));
//        $business = $data['business']['business'];
//        $business->update($business);
//        $business->locales()->delete();
//        $business->locales()->createMany($data['business']['locales']);
//        $business->settings()->delete();
//        $business->settings()->createMany($data['business']['settings']);
        return $data;
    }

}
