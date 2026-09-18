<?php

namespace App\Http\Controllers;

use App\Repository\CouponRepositoryInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
     *
     * Required date fields:
     *   usage_start_date / usage_end_date  — the window during which the coupon may be used (today check).
     *
     * Optional date fields:
     *   reservation_start_date / reservation_end_date — when set, any order reservation must
     *   fall within this period for the coupon to be valid.
     */
    public function create(Request $request): JsonResponse
    {
        $data = $request->all();
        $data['business_id'] = $request->route('businessId');
        $data['branch_id']   = $request->route('branchId');

        $validator = Validator::make($data, [
            'code'                   => 'sometimes|string|max:64|unique:coupons,code',
            'discount_type'          => 'required|in:fixed,percentage',
            'discount_value'         => 'required|numeric|min:0',
            'usage_type'             => 'required|in:single,multi',
            'usage_limit'            => 'nullable|integer|min:1',
            'is_active'              => 'sometimes|boolean',
            // Usage window (required)
            'usage_start_date'       => 'required|date',
            'usage_end_date'         => 'required|date|after_or_equal:usage_start_date',
            // Reservation window (optional)
            'reservation_start_date' => 'nullable|date',
            'reservation_end_date'   => 'nullable|date|after_or_equal:reservation_start_date',
            'business_id'            => 'required|integer|exists:business,id',
            'branch_id'              => 'required|integer|exists:branches,id',
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

        $validator = Validator::make($data, [
            'code'                   => "sometimes|string|max:64|unique:coupons,code,{$id}",
            'discount_type'          => 'sometimes|in:fixed,percentage',
            'discount_value'         => 'sometimes|numeric|min:0',
            'usage_type'             => 'sometimes|in:single,multi',
            'usage_limit'            => 'nullable|integer|min:1',
            'is_active'              => 'sometimes|boolean',
            // Usage window
            'usage_start_date'       => 'sometimes|date',
            'usage_end_date'         => 'sometimes|date|after_or_equal:usage_start_date',
            // Reservation window
            'reservation_start_date' => 'nullable|date',
            'reservation_end_date'   => 'nullable|date|after_or_equal:reservation_start_date',
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
     *
     * Optional body params for reservation orders:
     *   reservation_start_date (Y-m-d) — earliest reservation start in the cart
     *   reservation_end_date   (Y-m-d) — latest reservation end in the cart
     */
    public function checkCode(Request $request): JsonResponse
    {
        $data = $request->all();
        $data['business_id'] = $request->route('businessId');
        $data['branch_id']   = $request->route('branchId');

        $validator = Validator::make($data, [
            'code'                   => 'required|string',
            'subtotal'               => 'required|numeric|min:0',
            'business_id'            => 'required|integer',
            'branch_id'              => 'required|integer|exists:branches,id',
            'reservation_start_date' => 'nullable|date',
            'reservation_end_date'   => 'nullable|date|after_or_equal:reservation_start_date',
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
            (int) $data['branch_id'],
            $data['reservation_start_date'] ?? null,
            $data['reservation_end_date']   ?? null,
        );

        return response()->json($snapshot);
    }
}
