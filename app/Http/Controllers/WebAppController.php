<?php

namespace App\Http\Controllers;


use App\Constants\BusinessTypes;
use App\Constants\MenuTypes;
use App\Models\Branch;
use App\Models\Menu;
use App\Repository\BusinessRepositoryInterface;
use App\Repository\MenuRepositoryInterface;
use Illuminate\Http\JsonResponse;

class WebAppController extends Controller
{
    public function __construct(private readonly BusinessRepositoryInterface    $businessRepository,
                                private readonly MenuRepositoryInterface $menuRepository)
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
        if($branchMenu->type === MenuTypes::SUBSCRIPTION)
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

    public function version(): JsonResponse
    {
        return response()->json([
            "latest-version" => env("WEB_APP_LATEST_VERSION"),
            "should-update" => env("WEB_APP_SHOULD_UPDATE"),
            "must-update" => env("WEB_APP_MUST_UPDATE"),
            "min-acceptable-version" => env("WEB_APP_MIN_ACCEPTABLE_VERSION"),
        ]);
    }

    public function businessTypes(): JsonResponse {
        return response()->json(BusinessTypes::all());
    }

}
