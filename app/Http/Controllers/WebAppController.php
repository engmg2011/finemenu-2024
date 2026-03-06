<?php

namespace App\Http\Controllers;


use App\Constants\BusinessTypes;
use App\Constants\MenuTypes;
use App\Models\Branch;
use App\Models\Device;
use App\Models\Menu;
use App\Notifications\OneSignalNotification;
use App\Repository\AreaRepositoryInterface;
use App\Repository\BranchRepositoryInterface;
use App\Repository\BusinessRepositoryInterface;
use App\Repository\CategoryRepositoryInterface;
use App\Repository\Eloquent\Itemable\Cars\CarBrandRepository;
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
use Storage;

class WebAppController extends Controller
{
    public function __construct(private readonly BusinessRepositoryInterface $businessRepository,
                                private readonly MenuRepositoryInterface     $menuRepository)
    {

    }

    /**
     * Display a listing of the resource.
     *
     * @param $menuId
     * @return JsonResponse
     */
    public function nestedMenu($menuId): JsonResponse
    {
        $menu = $this->menuRepository->fullMenu($menuId);
        return response()->json($menu);
    }


    /**
     * Display a listing of the resource.
     *
     * @param $branchSlug
     * @return JsonResponse
     */
    public function branchMenu($branchSlug): JsonResponse
    {
        $branch = Branch::with(['locales', 'settings', 'media',
            'business.locales', 'business.media',
            'business.settings'])->where('slug', $branchSlug)->firstOrFail();
        $branchMenu = Menu::find($branch->menu_id);
        if ($branchMenu->type === MenuTypes::SUBSCRIPTION)
            return response()->json($this->businessRepository->dietMenu($branchMenu, $branch));
        $menu = $this->menuRepository->fullMenu($branch->menu_id);
        return response()->json(compact('branch', 'menu'));
    }

    /**
     * Display a listing of the resource.
     *
     * @param $branchSlug
     * @return JsonResponse
     */
    public function menuType($branchSlug): JsonResponse
    {
        $branch = Branch::with(['menu'])->where('slug', $branchSlug)->firstOrFail();
        return response()->json(['type' => $branch->menu->type]);
    }

    public function QRAppVersion(): JsonResponse
    {
        return response()->json([
            "latest-version" => env("QR_APP_LATEST_VERSION"),
            "should-update" => env("QR_APP_SHOULD_UPDATE"),
            "must-update" => env("QR_APP_MUST_UPDATE"),
            "min-acceptable-version" => env("QR_APP_MIN_ACCEPTABLE_VERSION"),
        ]);
    }

    public function TabletAppVersion(): JsonResponse
    {
        return response()->json([
            "latest-version" => env("TABLET_APP_LATEST_VERSION"),
            "should-update" => env("TABLET_APP_SHOULD_UPDATE"),
            "must-update" => env("TABLET_APP_MUST_UPDATE"),
            "min-acceptable-version" => env("TABLET_APP_MIN_ACCEPTABLE_VERSION"),
        ]);
    }

    public function OrdersAppVersion(): JsonResponse
    {
        return response()->json([
            "latest-version" => env("ORDERS_APP_LATEST_VERSION"),
            "should-update" => env("ORDERS_APP_SHOULD_UPDATE"),
            "must-update" => env("ORDERS_APP_MUST_UPDATE"),
            "min-acceptable-version" => env("ORDERS_APP_MIN_ACCEPTABLE_VERSION"),
        ]);
    }

    public function businessTypes(): JsonResponse
    {
        return response()->json(BusinessTypes::all());
    }

    public function send()
    {
        Device::find(25)->notify(new OneSignalNotification("hi", "test"));
    }

    public function carBrands()
    {
        return response()->json(app(CarBrandRepository::class)->listModel());
    }


    public function backup($businessId)
    {
        $usersBup = app(UserRepositoryInterface::class)->backup($businessId);
        $businessBup = app(BusinessRepositoryInterface::class)->backup($businessId);
//        $branchesBup = app(BranchRepositoryInterface::class)->backup($businessId);
//        $menusBup = app(MenuRepositoryInterface::class)->backup($businessId);
//        $categoriesBup = app(CategoryRepositoryInterface::class)->backup($businessId);
//        $categoryIds = $categoriesBup['categories']->pluck('id')->toArray();
//        $itemsBup = app(ItemRepositoryInterface::class)->backup($categoryIds);
//        $reservationsBup = app(ReservationRepositoryInterface::class)->backup($businessId);
//        $invoicesBup = app(InvoiceRepositoryInterface::class)->backup($businessId);
//        $auditBup = app(AuditService::class)->backup($businessId);
//        $areasBup = app(AreaRepositoryInterface::class)->backup($businessId);
//        $seatsBup = app(SeatRepositoryInterface::class)->backup($businessId);

        $data = [
            'users' => $usersBup,
            'business' => $businessBup,
//            'branches' => $branchesBup,
//            'menus' => $menusBup,
//            'categories' => $categoriesBup,
//            'items' => $itemsBup,
//            'reservations' => $reservationsBup,
//            'invoices' => $invoicesBup,
//            'audit' => $auditBup,
//            'areas' => $areasBup,
//            'seats' => $seatsBup,
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
//        return $data ;

        $res = app(BusinessRepositoryInterface::class)->restore($data['business']);
//        $res = app(UserRepositoryInterface::class)->restore($data['users']);

//        app(BranchRepositoryInterface::class)->restore($data['businessId']);
//        app(MenuRepositoryInterface::class)->restore($data['businessId']);
//        app(CategoryRepositoryInterface::class)->restore($data['businessId']);
//
//        app(ItemRepositoryInterface::class)->restore($data['categoryIds']);
//        app(ReservationRepositoryInterface::class)->restore($data['businessId']);
//        app(InvoiceRepositoryInterface::class)->restore($data['businessId']);
//        app(AuditService::class)->restore($data['businessId']);
//        app(AreaRepositoryInterface::class)->restore($data['businessId']);
//        app(SeatRepositoryInterface::class)->restore($data['businessId']);
        return $res;
    }


}
