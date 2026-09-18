<?php

namespace App\Http\Controllers;

use App\Repository\CouponRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CouponsController extends Controller
{
    public function __construct(protected CouponRepositoryInterface $couponRepository)
    {
    }

    /**
     * List coupons for a business (optionally filtered by branch).
     */
    public function index(): JsonResponse
    {
        return response()->json($this->couponRepository->list());
    }

    /**
     * Show a single coupon.
     */
    public function show(int $id): JsonResponse
    {
        $coupon = $this->couponRepository->get($id);
        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found.'], 404);
        }
        return response()->json($coupon);
    }

    /**
     * Create a new coupon (admin / business owner).
     */
    public function create(Request $request): JsonResponse
    {
        $data = $request->all();
        $data['business_id'] = $request->route('businessId');
        $data['branch_id']   = $request->route('branchId');

        $validator = \Illuminate\Support\Facades\Validator::make($data, [
            'code'           => 'sometimes|string|max:64|unique:coupons,code',
            'discount_type'  => 'required|in:fixed,percentage',
            'discount_value' => 'required|numeric|min:0',
            'usage_type'     => 'required|in:single,multi',
            'usage_limit'    => 'nullable|integer|min:1',
            'is_active'      => 'sometimes|boolean',
            'start_date'     => 'required|date',
            'end_date'       => 'required|date|after_or_equal:start_date',
            'business_id'    => 'required|integer|exists:business,id',
            'branch_id'      => 'required|integer|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error.', 'errors' => $validator->errors()], 422);
        }

        // Auto-generate code if not provided
        if (empty($data['code'])) {
            $data['code'] = strtoupper(Str::random(8));
        }

        return response()->json($this->couponRepository->createModel($data), 201);
    }

    /**
     * Update an existing coupon (admin / business owner).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->all();

        $validator = \Illuminate\Support\Facades\Validator::make($data, [
            'code'           => "sometimes|string|max:64|unique:coupons,code,{$id}",
            'discount_type'  => 'sometimes|in:fixed,percentage',
            'discount_value' => 'sometimes|numeric|min:0',
            'usage_type'     => 'sometimes|in:single,multi',
            'usage_limit'    => 'nullable|integer|min:1',
            'is_active'      => 'sometimes|boolean',
            'start_date'     => 'sometimes|date',
            'end_date'       => 'sometimes|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error.', 'errors' => $validator->errors()], 422);
        }

        return response()->json($this->couponRepository->updateModel($id, $data));
    }

    /**
     * Delete a coupon (admin only).
     */
    public function destroy(int $id): JsonResponse
    {
        $this->couponRepository->destroy($id);
        return response()->json(['message' => 'Coupon deleted.']);
    }

    /**
     * Check a coupon code for a user before placing an order.
     * Returns the discount snapshot without recording a redemption.
     */
    public function checkCode(Request $request): JsonResponse
    {
        $data = $request->all();
        $data['business_id'] = $request->route('businessId');
        $data['branch_id']   = $request->route('branchId');

        $validator = \Illuminate\Support\Facades\Validator::make($data, [
            'code'        => 'required|string',
            'subtotal'    => 'required|numeric|min:0',
            'business_id' => 'required|integer',
            'branch_id'   => 'required|integer|exists:branches,id',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error.', 'errors' => $validator->errors()], 422);
        }

        $userId   = auth('sanctum')->id();
        $snapshot = $this->couponRepository->applyCoupon(
            $data['code'],
            (float) $data['subtotal'],
            $userId,
            (int) $data['business_id'],
            (int) $data['branch_id']
        );

        return response()->json($snapshot);
    }
}
