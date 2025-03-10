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

    public const Relations = ['prices','invoices'];

    public function __construct(OrderLine $model,
                                private ReservationRepositoryInterface $reservationRepository)
    {
        parent::__construct($model);
    }

    public function process(array $data): array
    {
        return array_only($data, ['user_id', 'note', 'order_id', 'item_id', 'count', 'price_id', 'data',
            'total_price', 'subtotal_price']);
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
            ? Item::with('locales' ,'media', 'itemable')
                ->with(['prices' => fn($q) => $q->with('locales')->where('id', $data['price_id'])])
                ->find($data['item_id'])
            : Item::with('locales' ,'media', 'itemable')->find($data['item_id']);
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
            $insurance = isset($item->itemable) ? $item->itemable->insurance : 0;
            $orderLine['subtotal_price'] = $item->prices[0]->price + $addonsPrice + $insurance;
            $orderLine['total_price'] = $item->prices[0]->price + $addonsPrice + $insurance - $discountAmount;
            $orderLine['data'] += [
                'subtotal_price' => $orderLine['subtotal_price'],
                'total_price' => $orderLine['total_price'],
            ];
        }

        OrderLine::find($orderLine->id)->update([
            'subtotal_price' => $orderLine['subtotal_price'] ?? 0,
            'total_price' => $orderLine['total_price'] ?? 0,
            'data' => $orderLine['data']
        ]);

        if(isset($data['reservation'])) {
            $this->reservationRepository->set($item, $orderLine, $data['reservation']);
        }

    }

    public function createModel(array $data)
    {
        $data['user_id'] = auth('sanctum')->user()->id;
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
        return OrderLine::with(OrderLineRepository::Relations)->orderByDesc('id')->get();
    }

    public function get(int $id)
    {
        return OrderLine::with(OrderLineRepository::Relations)->find($id);
    }
}
