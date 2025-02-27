<?php

namespace App\Repository\Eloquent;


use App\Actions\DiscountAction;
use App\Constants\OrderStatus;
use App\Constants\PaymentConstants;
use App\Constants\PermissionsConstants;
use App\Events\NewOrder;
use App\Events\UpdateOrder;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Category;
use App\Models\Item;
use App\Models\Order;
use App\Models\Price;
use App\Models\User;
use App\Repository\InvoiceRepositoryInterface;
use App\Repository\OrderRepositoryInterface;
use App\Repository\ReservationRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{

    public const Relations = ['discounts.locales', 'orderlines.reservation', 'device', 'invoices'];

    public function __construct(Order                              $model,
                                private LocaleRepository           $localeRepository,
                                private OrderLineRepository        $orderLineRepository,
                                private PriceRepository            $priceAction,
                                private DiscountAction             $discountAction,
                                private InvoiceRepositoryInterface $invoiceRepository,
                                private ReservationRepositoryInterface $reservationRepository,
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
     * @throws \Exception
     */
    public function validateCategoriesInBranch(&$orderLines)
    {
        $categoryIds = [];
        foreach ($orderLines as &$orderLine) {
            $item = Item::select('category_id')->find($orderLine['item_id']);
            if($item && $item->category_id )
                $categoryIds[] = $item->category_id;
        }

        if(!count($categoryIds)) return false;

        $branchId = request()->route('branchId');
        $menuId = Branch::find($branchId)->menu_id;
        $menuIds = Category::whereIn('id', $categoryIds)->pluck('menu_id')->toArray();
        if (count($menuIds) > 1 || $menuId !== $menuIds[0])
            abort(400,"Wrong Data");
    }

    public function validatePrices(&$orderLines)
    {
        foreach ($orderLines as &$orderLine) {
            if($orderLine['price_id']){
                $priceable = Price::find($orderLine['price_id'])->priceable;
                if(!$priceable || get_class($priceable) !== Item::class || $orderLine['item_id'] !== $priceable->id)
                    abort(400,"Wrong Data");
            }
        }
    }

    /**
     * @throws \Exception
     */
    public function create(array $data): Model
    {
        $branchId = request()->route()->parameter('branchId');
        $businessId = request()->route('businessId');
        $data['orderable_id'] = $branchId;
        $data['orderable_type'] = get_class(new Branch());

        if(!count($data['order_lines']))
            abort(400,"Please add order content");

        $this->validateCategoriesInBranch($data['order_lines']);
        $this->validatePrices($data['order_lines']);
        $reservationData = $data['order_lines'][0]['reservation'];
        $sameUserReservation = null;
        if( isset($reservationData)){
            $reservationData['reservable_id'] = $data['order_lines'][0]['item_id'];
            $currentReservation = $this->reservationRepository->currentReservation($reservationData, $businessId ,$branchId);
            if($currentReservation){
                if(  $currentReservation->status === PaymentConstants::RESERVATION_COMPLETED  ||
                    $currentReservation->reserved_for_id =! auth()->user()->id ){
                    abort(400,"Not available now, please choose different dates or try again later");
                }
                $sameUserReservation = $currentReservation;
            }
        }
        if($sameUserReservation)
            return $this->get($sameUserReservation->order_id);

        $data['user_id'] = auth('sanctum')->user()->id;
        $data['status'] = $data['status'] ?? OrderStatus::Pending;
        $model = $this->model->create($this->process($data));
        $data['total_price'] = 0;
        $data['subtotal_price'] = 0;
        $orderLines = $this->orderLineRepository->createManyOLs($model->id, $data['order_lines']);
        foreach ($orderLines as &$orderLine) {
            $data['total_price'] += $orderLine->total_price;
            $data['subtotal_price'] += $orderLine->subtotal_price;
        }

        $this->setOrderData($model, $data);
        $model->update(['total_price' => $data['total_price'],
            'subtotal_price' => $data['subtotal_price']]);
        if (isset($data['invoice']) && $data['invoice'] && $data['total_price'] > 0) {
            $this->invoiceRepository->setForOrder($model, $data['invoice']);
        }
        // Send Event
        event(new NewOrder($model->id));
        return $this->get($model->id);
    }

    public function setOrderData(&$model, &$data)
    {
        if (isset($data['locales']))
            $this->localeRepository->setLocales($model, $data['locales']);
        if (isset($data['prices']))
            $this->priceAction->setPrices($model, $data['prices']);
        if (isset($data['addons']))
            $this->discountAction->set($model, $data['addons']);
        if (isset($data['discounts']))
            $this->discountAction->set($model, $data['discounts']);
    }

    public function update($id, array $data): Model
    {
        // Check the user has the authority to make this order paid (admin | owner | user )
        $userId = auth('sanctum')->user()->id;
        $user = User::find($userId);
        $order = Order::find($id);
        if (!$user->hasAnyPermission($this->getOrderRequiredPermission($order)))
            abort(403,'You Don\'t have permission');

        // TODO:: check if data['paid']
        $model = tap($this->model->find($id))
            ->update($this->process($data));
        $this->setOrderData($model, $data);

        // Send evenxt
        event(new UpdateOrder($model->id));

        if (isset($data['status']) && $data['status'] === OrderStatus::Ready) {
            $userDevices = User::find($model->user_id)->devices()->get();
            /*foreach ($userDevices as $device) {
                if($device->onesignal_token)
                    $device->notify(new OneSignalNotification('MenuAI', 'Your order became ready 😋'));
            }*/
        }
        if (isset($data['order_lines']))
            $this->orderLineRepository->updateMany($data['order_lines']);
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
