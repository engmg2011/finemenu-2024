<?php


namespace App\Actions;


use App\Constants\OrderStatus;
use App\Constants\PermissionsConstants;
use App\Constants\RolesConstants;
use App\Events\SendOrders;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\User;
use App\Notifications\OneSignalNotification;
use App\Repository\Eloquent\OrderRepository;
use Illuminate\Database\Eloquent\Model;

class OrderAction
{
    public $orderItemRelations = [
        'prices.locales', 'discounts.locales',
        'orderLines.prices.locales', 'orderLines.item.locales',
        'orderLines.addons.locales', 'orderLines.addons.prices', 'orderLines.discounts.locales'
    ];

    public function __construct(private OrderRepository $repository, private OrderLineAction $orderLineAction,
                                private PriceAction     $priceAction,
                                private DiscountAction  $discountAction)
    {
    }

    public function process(array $data): array
    {
        $data['user_id'] = auth('api')->user()->id;
        $data['status'] = $data['status'] ?? OrderStatus::Pending;
        return array_only($data, ['user_id', 'note', 'orderable_id',
            'orderable_type', 'scheduled_at', 'status', 'paid']);
    }

    public function create(array $data)
    {
        $model = $this->repository->create($this->process($data));
        foreach ($data['order_lines'] as &$ol) {
            $ol['order_id'] = $model->id;
            $this->orderLineAction->create($ol);
        }
        $this->setOrderData($model, $data);
        return $model;
    }

    public function setOrderData(&$model, &$data)
    {
        if (isset($data['prices']))
            $this->priceAction->setPrices($model, $data['prices']);
        if (isset($data['discounts']))
            $this->discountAction->set($model, $data['discounts']);
        event(new SendOrders($model->orderable_id));
    }

    public function update($id, array $data): Model
    {
        // Check the user has the authority to make this order paid (admin | owner | user )
        $userId = auth('api')->user()->id;
        $user = User::find($userId);
        $order = Order::find($id);
        if (!($user->hasRole('admin') || $order->user_id === $userId ||
            $user->hasAnyDirectPermission($this->getOrderPermittedRules($order))))
            return throw new \HttpException('You Don\'t have permission', 403);

        // TODO:: check if data['paid']
        $model = tap($this->repository->find($id))
            ->update($this->process($data));
        $this->setOrderData($model, $data);
        if ($data['status'] === OrderStatus::Ready) {
            \Log::debug("will send message");
            User::find($model->user_id)->notify(new OneSignalNotification('FineMenu', 'Your order became ready 😋'));
        }
        if (isset($data['order_lines']))
            $this->orderLineAction->updateMany($data['order_lines']);
        return $model;
    }

    /**
     * @return mixed
     */
    public function list($conditions = null)
    {
        $restaurantId = request()->header('restaurant-id');
        return Order::with($this->orderItemRelations)
            ->orderByDesc('id')
            ->where(fn($q) => $conditions ? $q->where(...$conditions) : $q)
            ->where(fn($q) => $restaurantId ?
                $q->where(['orderable_id' => $restaurantId, 'orderable_type' => '\\App\\Models\\Restaurant']) : $q)
            ->paginate(request('per-page', 15));
    }

    public function kitchenOrders($restaurantId = null)
    {
        $restaurantId = $restaurantId ?? request()->header('restaurant-id');
        return Order::with($this->orderItemRelations)
            ->where(fn($q) => $restaurantId ?
                $q->where(['orderable_id' => $restaurantId, 'orderable_type' => '\\App\\Models\\Restaurant']) : $q)
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

    public function get(int $id)
    {
        return Order::with('orderLines.prices')->find($id);
    }

    public function getOrderPermittedRules(&$order): array
    {
        $orderableType = $order->orderable_type === '\APP\Models\Restaurant' ?
            PermissionsConstants::Restaurants : PermissionsConstants::Hotels;
        return [
            $orderableType . '.' . RolesConstants::OWNER . '.' . $order->orderable()->id,
            $orderableType . '.' . RolesConstants::KITCHEN . '.' . $order->orderable()->id,
            $orderableType . '.' . RolesConstants::DRIVER . '.' . $order->orderable()->id,
            $orderableType . '.' . RolesConstants::CASHIER . '.' . $order->orderable()->id,
        ];
    }
}
