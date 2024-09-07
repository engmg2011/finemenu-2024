<?php

namespace App\Repository\Eloquent;


use App\Actions\DiscountAction;
use App\Actions\OrderLineAction;
use App\Constants\OrderStatus;
use App\Constants\RolesConstants;
use App\Events\NewOrder;
use App\Events\UpdateOrder;
use App\Models\Branch;
use App\Models\Business;
use App\Models\Order;
use App\Models\User;
use App\Repository\OrderRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class OrderRepository extends BaseRepository implements OrderRepositoryInterface
{

    public const Relations = ['orderLines.locales', 'locales', 'prices.locales', 'discounts.locales',
        'orderLines.prices.locales', 'orderLines.item.locales',
        'orderLines.addons.locales', 'orderLines.addons.prices', 'orderLines.discounts.locales'];

    /**
     * UserRepository constructor.
     * @param Order $model
     */
    public function __construct(Order                    $model,
                                private LocaleRepository $localeRepository,
                                private OrderLineAction  $orderLineAction,
                                private PriceRepository  $priceAction,
                                private DiscountAction   $discountAction
    )
    {
        parent::__construct($model);
    }

    public function get($id)
    {
        return $this->model->with(OrderRepository::Relations)->find($id);
    }

    public function branchOrders($businessId)
    {
        return $this->model->where([
            'orderable_type' => Branch::class,
            'orderable_id' => $businessId
        ])->with(OrderRepository::Relations)->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function process(array $data): array
    {
        $data['user_id'] = auth('api')->user()->id;
        $data['status'] = $data['status'] ?? OrderStatus::Pending;
        return array_only($data, ['user_id', 'note', 'orderable_id',
            'orderable_type', 'scheduled_at', 'status', 'paid']);
    }

    public function create(array $data): Model
    {
        $data['orderable_id'] = request()->route()->parameter('branchId');
        $data['orderable_type'] = get_class(new Branch());

        $model = $this->model->create($this->process($data));
        $totalPrice = 0;
        foreach ($data['order_lines'] as &$ol) {
            $ol['order_id'] = $model->id;
            $orderLine = $this->orderLineAction->create($ol);
            if (isset($orderLine->prices[0]))
                $totalPrice += $orderLine->prices[0]->price;
        }
        $data['prices'] = [];
        $data['prices'][] = [
            'price' => $totalPrice
        ];
        $this->setOrderData($model, $data);
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
        if (isset($data['discounts']))
            $this->discountAction->set($model, $data['discounts']);
    }

    public function update($id, array $data): Model
    {
        // Check the user has the authority to make this order paid (admin | owner | user )
        $userId = auth('api')->user()->id;
        $user = User::find($userId);
        $order = Order::find($id);
        if ( $user->hasPermissionTo($this->getOrderRequiredPermission($order)))
            return throw new \Exception('You Don\'t have permission', 403);

        // TODO:: check if data['paid']
        $model = tap($this->model->find($id))
            ->update($this->process($data));
        $this->setOrderData($model, $data);

        // Send event
        event(new UpdateOrder($model->id));

        if (isset($data['status']) && $data['status'] === OrderStatus::Ready) {
//            User::find($model->user_id)->notify(new OneSignalNotification('FineMenu', 'Your order became ready 😋'));
        }
        if (isset($data['order_lines']))
            $this->orderLineAction->updateMany($data['order_lines']);
        return $this->get($model->id);
    }

    /**
     * @return mixed
     */
    public function list($conditions = null)
    {
        $branchId = request()->route('branchId');
        return Order::with(OrderRepository::Relations)
            ->orderByDesc('id')
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

    public function getOrderRequiredPermission(&$order): string
    {
        $or = explode('\\', get_class($order->orderable));
        $orderableType = strtolower(end($or));
        return $orderableType.'.'.$order->orderable->id;
    }
}
