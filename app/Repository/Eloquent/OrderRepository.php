<?php

namespace App\Repository\Eloquent;


use App\Constants\BusinessTypes;
use App\Constants\OrderStatus;
use App\Constants\PermissionActions;
use App\Constants\PermissionsConstants;
use App\Constants\PermissionServices;
use App\Events\NewOrder;
use App\Events\UpdateOrder;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Category;
use App\Models\Item;
use App\Models\Items\SalonProduct;
use App\Models\Order;
use App\Models\User;
use App\Repository\DiscountRepositoryInteface;
use App\Repository\InvoiceRepositoryInterface;
use App\Repository\OrderRepositoryInterface;
use App\Repository\ReservationRepositoryInterface;
use Exception;
use Illuminate\Database\Eloquent\Model;
use function PHPUnit\Framework\isEmpty;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{

    public const Relations = ['discounts.locales', 'orderlines.reservation', 'device', 'invoices'];

    public function __construct(Order                                           $model,
                                private readonly LocaleRepository               $localeRepository,
                                private readonly OrderLineRepository            $orderLineRepository,
                                private readonly PriceRepository                $priceAction,
                                private readonly DiscountRepositoryInteface     $discountRepository,
                                private readonly InvoiceRepositoryInterface     $invoiceRepository,
                                private readonly ReservationRepositoryInterface $reservationRepository,
    )
    {
        parent::__construct($model);
    }

    public function process(array $data): array
    {
        return array_only($data, ['user_id', 'note', 'orderable_id', 'total_price', 'subtotal_price',
            'orderable_type', 'scheduled_at', 'status', 'paid', 'device_id', 'delivery_address']);
    }

    public function get($id)
    {
        return $this->model->with(OrderRepository::Relations)->find($id);
    }

    public function branchOrders($branchId)
    {
        return $this->model->where([
            'orderable_type' => Branch::class,
            'orderable_id' => $branchId
        ])->with(OrderRepository::Relations)
            ->with('orderable.business.locales', 'orderable.locales')
            ->orderByDesc('id')->paginate(request('per-page', 10));
    }

    public function userOrders()
    {
        $branchId = request('branch_id');
        return $this->model->where(
            ['user_id' => auth()->id()]
            + ($branchId ? [
                "orderable_type" => Branch::class,
                "orderable_id" => $branchId] : [])
        )->with(OrderRepository::Relations)
            ->with('orderable.business.locales', 'orderable.locales')
            ->orderByDesc('id')->paginate(request('per-page', 5));
    }

    /**
     * @param $orderLines
     * @return false|void
     */
    public function validateCategoriesInBranch(&$orderLines)
    {
        $categoryIds = [];
        foreach ($orderLines as &$orderLine) {
            $item = Item::select('category_id')->find($orderLine['item_id']);
            if ($item && $item->category_id)
                $categoryIds[] = $item->category_id;
        }

        if (!count($categoryIds)) return false;

        $branchId = request()->route('branchId');
        $menuId = Branch::find($branchId)->menu_id;
        $menuIds = Category::whereIn('id', $categoryIds)->pluck('menu_id')->unique()->toArray();
        if (count($menuIds) > 1 || $menuId !== $menuIds[0])
            abort(400, "Wrong Data");
    }

    public function checkAllowedReservationsOrDie(&$data, $business, $branchId)
    {
        if (isset($data['order_lines']) && is_array($data['order_lines'])) {
            for ($i = 0; $i <= count($data['order_lines']); $i++) {

                if (isset($data['order_lines'][$i]['reservation'])) {

                    $reservationData = $data['order_lines'][$i]['reservation'];
                    $reservable_id = $data['order_lines'][$i]['item_id'];

                    // check if user created same reservation before and not paid
                    $sameUserReservation = $this->reservationRepository->getSameReservation($reservationData, $reservable_id, $business->id, $branchId);
                    if ($sameUserReservation)
                        return $this->get($sameUserReservation->order_id);

                    // check if item exceeds the allowed amount
                    $reservationData['reservable_id'] = $reservable_id;
                    if ($business->type === BusinessTypes::CHALET) {
                        $this->reservationRepository->checkAllowedReservationUnits($reservationData, $business->id, $branchId);
                    }

                    if($business->type === BusinessTypes::SALON) {
                        $this->reservationRepository->isFollowerAvailable($reservationData, $business->id, $branchId);
                    }

                }

            }
        }
    }

    public function validateProductsData(&$orderLines)
    {
        if (isset($orderLines) && is_array($orderLines)) {

            $itemIds = array_column($orderLines, 'item_id');
            $items = Item::with('itemable')
                ->where('hide', false)
                ->where('disable_ordering', false)
                ->whereIn('id', $itemIds)->get();

            if (count($items) !== count($itemIds))
                abort(400, "Wrong Data");

            foreach ($items as &$item) {
                // Check if the item is a salon product and if the amount is less than the order line count
                if ($item->itemable instanceof SalonProduct) {
                    $orderLine = collect($orderLines)->first(function ($line) use ($item) {
                        return $line['item_id'] == $item->id;
                    });
                    $orderLine['count'] = (int) $orderLine['count'] ?? 1;
                    if ($item->itemable->amount < $orderLine['count']) {
                        abort(400, "Sorry, this amount is out of stock");
                    }
                }
            }
        }
    }

    /**
     * @throws Exception
     */
    public function create(array $data, $business = null): Model
    {
        if (!$business)
            $business = Business::find($data['business_id']);

        $data['orderable_id'] = $data['branch_id'];
        $data['orderable_type'] = get_class(new Branch());
        if (!isset($data['order_lines']) || !count($data['order_lines']))
            abort(400, "Please add order content");

        $this->validateCategoriesInBranch($data['order_lines']);

        // checking if exists reservation
        $this->checkAllowedReservationsOrDie($data, $business, $data['branch_id']);

        // checking if items are available
        $this->validateProductsData($data['order_lines']);

        $data['user_id'] = auth('sanctum')->user()->id;
        $data['status'] = $data['status'] ?? OrderStatus::Pending;

        $model = $this->model->create($this->process($data));

        $orderLines = $this->orderLineRepository->createManyOLs($model->id, $data['order_lines']);
        $totalPrice = 0;
        $subtotalPrice = 0;
        foreach ($orderLines as &$orderLine) {
            $totalPrice += $orderLine->total_price;
            $subtotalPrice += $orderLine->subtotal_price;
        }
        $data['total_price'] = $totalPrice;
        $data['subtotal_price'] = $subtotalPrice;

        $this->setOrderData($model, $data);
        $model->update(['total_price' => $data['total_price'],
            'subtotal_price' => $data['subtotal_price']]);
        if (isset($data['invoice']) && $data['invoice'] && $data['total_price'] > 0) {
            $this->invoiceRepository->setForOrder($model, $data['invoice']);
        }

        // Todo :: remove[0] and search for any
        // Don't send if there is a reservation as it will be sent from NewReservation Event
        if (!isset($data['order_lines'][0]['reservation'])) {
            event(new NewOrder($model->id));
        }
        return $this->get($model->id);
    }

    public function setOrderData(&$model, &$data)
    {
        if (isset($data['locales']))
            $this->localeRepository->setLocales($model, $data['locales']);
        if (isset($data['prices']))
            $this->priceAction->setPrices($model, $data['prices']);
        if (isset($data['addons']))
            $this->discountRepository->set($model, $data['addons']);
        if (isset($data['discounts']))
            $this->discountRepository->set($model, $data['discounts']);
    }

    public function update($id, array $data): Model
    {
        // Check the user has the authority to make this order paid (admin | owner | user )
        $userId = auth('sanctum')->user()->id;
        $user = User::find($userId);
        $order = Order::find($id);
        if (!$user->hasAnyPermission([$this->getOrderRequiredPermission($order),
            "branch." . $order->orderable_id . "." . PermissionServices::Orders . "." . PermissionActions::Update
        ]))
            abort(403, 'You Don\'t have permission');

        // TODO:: check if data['paid']
        $model = tap($this->model->find($id))
            ->update($this->process($data));
        $this->setOrderData($model, $data);

        if (isset($data['order_lines']))
            $this->orderLineRepository->updateMany($data['order_lines']);

        // Todo :: remove[0] and search for any
        // Don't send if there is a reservation as it will be sent from NewReservation Event
        if (!isset($data['order_lines'][0]['reservation'])) {
            event(new UpdateOrder($model->id));
        }
        return $this->get($model->id);
    }

    /**
     * @return mixed
     */
    public function list($conditions = null)
    {
        $branchId = request()->route('branchId');
        return Order::with(OrderRepository::Relations)
            ->where(fn($q) => $conditions ? $q->where(...$conditions) : $q)
            ->where(fn($q) => $branchId ?
                $q->where(['orderable_id' => $branchId, 'orderable_type' => Branch::class]) : $q)
            ->orderByDesc('id')
            ->paginate(request('per-page', 15));
    }

    public function kitchenOrders($businessId = null)
    {
        $businessId = $businessId ?? request()->header('business-id');
        return Order::with(OrderRepository::Relations)
            ->where(fn($q) => $businessId ?
                $q->where(['orderable_id' => $businessId, 'orderable_type' => Business::class]) : $q)
            ->where('status', '!=', OrderStatus::Delivered)
            ->orderByDesc('id')
            ->paginate(10);
    }

    public function cashierOrders()
    {
        return $this->list(['paid', false]);
    }

    public function driverOrders()
    {
        return $this->list(['status', '!=', OrderStatus::Delivered]);
    }

    public function getOrderRequiredPermission(&$order): array
    {
        $or = explode('\\', get_class($order->orderable));
        $orderableType = strtolower(end($or));
        if ($orderableType === 'Branch') {
            $businessId = request()->route('businessId');
            return [$orderableType . '.' . $order->orderable->id, PermissionsConstants::Business . '.' . $businessId];
        }
        return [$orderableType . '.' . $order->orderable->id];
    }
}
