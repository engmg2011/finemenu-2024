<?php

namespace App\Http\Controllers;

use App\Http\Resources\DataResource;
use App\Models\Audit;
use Illuminate\Http\Request;

class AuditController extends Controller
{
    const Relations = ['user'];

    public function index(Request $request)
    {
        $businessId =  $request->route('businessId');
        $branchId =  $request->route('branchId');
        return DataResource::collection(
            Audit::where('business_id', $businessId)
                ->where('branch_id', $branchId)
                ->paginate(request('per-page', 50))
        );

    }

    public function filterRequest($request)
    {
        $branchId = request()->route('branchId');
        $businessId = request()->route('businessId');
        $query = Audit::query()->with(self::Relations);

        if ($request->has('service_type'))
            $query->where('service_type', 'like' , '%'.$request->service_type);

        if ($request->has('service_id'))
            $query->where('service_id', $request->service_id);

        if ($request->has('user_id'))
            $query->where('user_id', $request->user_id);

        if ($request->has('from') && $request->has('to'))
            $query->whereBetween('created_at',  [$request->from , $request->to ]);

        return $query->where(['branch_id' => $branchId, 'business_id' => $businessId])
            ->orderByDesc('id')
            ->paginate(request('per-page', 50));
    }

    public function filter(Request $request)
    {
        $ordersList = $this->filterRequest($request);
        return DataResource::collection($ordersList);
    }
}
