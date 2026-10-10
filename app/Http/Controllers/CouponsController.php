<?php

namespace App\Http\Controllers;

use App\Repository\CouponRepositoryInterface;
use App\Repository\Eloquent\CouponRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CouponsController extends Controller
{
    public function __construct(protected CouponRepositoryInterface $couponRepository)
    {
    }

    /**
     * List coupons for a branch.
     */
    public function index(Request $request): JsonResponse
    {
        return response()->json($this->couponRepository->list());
    }

    /**
     * Show a single coupon.
     */
    public function show(Request $request): JsonResponse
    {
        $id = (int) $request->route('id');

        $coupon = $this->couponRepository->get($id);
        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found.'], 404);
        }
        return response()->json($coupon);
    }

    /**
     * Create a new coupon.
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
        $data                = $this->normalizeCodes($request->all());
        $data['business_id'] = (int) $request->route('businessId');
        $data['branch_id']   = (int) $request->route('branchId');

        $validator = Validator::make($data, [
            // Manual coupon: one fixed code. Cannot be combined with generated codes.
            'code'                   => 'nullable|string|max:64|unique:coupon_codes,code|prohibits:code_prefix',
            // Generated codes: "{code_prefix}-{RANDOM}", e.g. ZAIN20-K7QM3XPA
            'code_prefix'            => 'nullable|string|max:20|regex:/^[A-Z0-9]+$/|required_with:codes_count',
            'codes_count'            => 'nullable|integer|min:1|max:5000',
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

        return response()->json($this->couponRepository->createModel($data), 201);
    }

    /**
     * Update an existing coupon.
     */
    public function update(Request $request): JsonResponse
    {
        $id   = (int) $request->route('id');
        $data = $this->normalizeCodes($request->all());

        $validator = Validator::make($data, [
            'code'                   => [
                'sometimes', 'string', 'max:64',
                Rule::unique('coupon_codes', 'code')->where(fn($q) => $q->where('coupon_id', '!=', $id)),
            ],
            'code_prefix'            => 'sometimes|nullable|string|max:20|regex:/^[A-Z0-9]+$/',
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
     * Delete a coupon.
     */
    public function destroy(Request $request): JsonResponse
    {
        $id = (int) $request->route('id');
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
        $data                = $request->all();
        $data['business_id'] = (int) $request->route('businessId');
        $data['branch_id']   = (int) $request->route('branchId');

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

        $userId   = (int) auth('sanctum')->id();
        $snapshot = $this->couponRepository->applyCoupon(
            $data['code'],
            (float) $data['subtotal'],
            $userId,
            $data['business_id'],
            $data['branch_id'],
            $data['reservation_start_date'] ?? null,
            $data['reservation_end_date']   ?? null,
        );

        return response()->json($snapshot);
    }

    /**
     * List a coupon's codes (paginated). Optional query: redeemed=1|0.
     */
    public function codes(Request $request): JsonResponse
    {
        $coupon = $this->findCouponInRoute($request);
        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found.'], 404);
        }

        return response()->json($this->couponRepository->listCodes($coupon->id));
    }

    /**
     * Generate more "{code_prefix}-{RANDOM}" codes for an existing coupon.
     * Body: { count }
     */
    public function generateCodes(Request $request): JsonResponse
    {
        $coupon = $this->findCouponInRoute($request);
        if (!$coupon) {
            return response()->json(['message' => 'Coupon not found.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'count' => 'required|integer|min:1|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Validation error.', 'errors' => $validator->errors()], 422);
        }

        $created = $this->couponRepository->generateCodes($coupon, (int) $request->input('count'));

        return response()->json([
            'message' => "{$created} codes generated.",
            'coupon'  => $this->couponRepository->get($coupon->id),
        ], 201);
    }

    /**
     * The coupon from the {id} route parameter, only if it belongs to the route's business.
     */
    protected function findCouponInRoute(Request $request)
    {
        $coupon = $this->couponRepository->get((int) $request->route('id'));

        if (!$coupon || (int) $coupon->business_id !== (int) $request->route('businessId')) {
            return null;
        }

        return $coupon;
    }

    /**
     * Codes are case-insensitive: store and compare them uppercase.
     */
    protected function normalizeCodes(array $data): array
    {
        foreach (['code', 'code_prefix'] as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                $data[$field] = CouponRepository::normalizeCode($data[$field]);
            }
        }
        return $data;
    }
}
