<?php

namespace App\Repository\Eloquent;


use App\Constants\DiscountTypes;
use App\Models\Addon;
use App\Models\Discount;
use App\Models\Item;
use App\Models\OrderLine;
use App\Repository\OrderLineRepositoryInterface;
use App\Repository\ReservationRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class OrderLineRepository extends BaseRepository implements OrderLineRepositoryInterface
{
    /**
     * UserRepository constructor.
     * @param OrderLine $model
     */
    public function __construct(OrderLine $model,
                                private ReservationRepositoryInterface $reservationRepository)
    {
        parent::__construct($model);
    }

    public function process(array $data): array
    {
        $data['user_id'] = auth('sanctum')->user()->id;
        return array_only($data, ['user_id', 'note', 'order_id', 'item_id', 'count', 'price_id', 'data', 'total_price', 'subtotal_price']);
    }

    /**
     * @param $orderLine :: the created or updated orderLine
     * @param $data
     * @param bool $create
     * @return void
     */
    public function processRelations(&$orderLine, &$data, bool $create = false)
    {
        $orderLine['data'] = [];
        $addonsPrice = 0;
        $discountAmount = 0;

        //orderLine item data
        $item = isset($data['price_id'])
            ? Item::with('locales' ,'media')
                ->with(['prices' => fn($q) => $q->with('locales')->where('id', $data['price_id'])])
                ->find($data['item_id'])
            : Item::with('locales' ,'media')->find($data['item_id']);
        $orderLine['data'] += ["item" => $item , "user" => auth()->user()];

        // orderLine addons data
        if (isset($data['addon_ids'])) {
            $addons = Addon::with('locales')->whereIn('id', $data['addon_ids'])
                ->select('id', 'price')->get()?->toArray();
            foreach ($addons as &$addon) {
                $addonsPrice += $addon['price'];
            }
            $orderLine['data'] += ["addons" => $addons];
        }

        // orderLine discounts data
        if (isset($data['price_id']) && isset($data['discount_ids']) && count($data['discount_ids'])) {
            // first discount only applied
            $discount = Discount::with('locales')->find($data['discount_ids'][0]);
            $discountAmount = $discount->type == DiscountTypes::VALUE ? $discount->amount : $item->prices[0]->price * $discount->amount / 100;
            $orderLine['data'] += ['discounts' => [$discount]];
        }

        if(isset($item->prices) && count($item->prices)) {
            $orderLine['subtotal_price'] = $item->prices[0]->price + $addonsPrice;
            $orderLine['total_price'] = $item->prices[0]->price + $addonsPrice - $discountAmount;
        }

        OrderLine::find($orderLine->id)->update([
            'subtotal_price' => $orderLine['subtotal_price'] ?? 0,
            'total_price' => $orderLine['total_price'] ?? 0,
            'data' => $orderLine['data']
        ]);

        if(isset($data['reservation']) && is_array($data['reservation'])) {
            $reservationData =$data['reservation'] + [
                'reservable_id' => $item->id,
                'reservable_type' => Item::class,
                'orderline_id' => $orderLine->id,
                'order_id' => $orderLine->order_id,
                'item_id' => $orderLine->item_id,
                'reservation_for_id' => $orderLine->user_id,
                'data' => $orderLine->data
            ];
            $this->reservationRepository->create($reservationData);
        }


    }

    public function createModel(array $data)
    {
        $orderLine = $this->create($this->process($data));
        $this->processRelations($orderLine, $data, true);
        return $orderLine;
    }

    public function createManyOLs($orderId, array $data): array
    {
        $orderLines = [];
        foreach ($data as &$ol) {
            $ol['order_id'] = $orderId;
            $orderLines[] = $this->createModel($ol);
        }
        return $orderLines;
    }

    public function update($id, array $data): Model
    {
        $orderLine = tap($this->find($id))
            ->update($this->process($data));
        $this->processRelations($orderLine, $data);
        return $orderLine;
    }

    public function updateMany($orderLines)
    {
        $arr = [];
        foreach ($orderLines as &$orderLine) {
            $arr = $this->updateOrCreate(['id' => $orderLine['id']], $orderLine);
        }
        return $arr;
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return OrderLine::with('prices')->orderByDesc('id')->get();
    }

    public function get(int $id)
    {
        return OrderLine::with('prices')->find($id);
    }
}
