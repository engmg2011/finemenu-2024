<?php

namespace App\Repository\Eloquent;


use App\Constants\DiscountTypes;
use App\Models\Item;
use App\Models\OrderLine;
use App\Models\Price;
use App\Repository\OrderLineRepositoryInterface;
use App\Repository\ReservationRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Spatie\Period\Period;

class OrderLineRepository extends BaseRepository implements OrderLineRepositoryInterface
{

    public const Relations = ['prices', 'invoices'];

    public function __construct(OrderLine                              $model,
                                private ReservationRepositoryInterface $reservationRepository)
    {
        parent::__construct($model);
    }

    public function process(array $data): array
    {
        return array_only($data, ['user_id', 'note', 'order_id', 'item_id', 'count', 'price_id', 'data',
            'total_price', 'subtotal_price']);
    }

    public function getItemWithRelations(&$data): Item
    {
        $itemQuery = Item::query();
        $itemQuery = $itemQuery->with('locales', 'itemable')
            ->with(['addons' => function ($query) use ($data) {
                if (isset($data['addon_ids'])) {
                    $query->whereIn('id', $data['addon_ids']);
                };
            }])
            ->with(['discounts' => function ($query) use ($data) {
                if (isset($data['discount_ids'])) {
                    $query->whereIn('id', $data['discount_ids']);
                    if (isset($data['reservation'])) {
                        $startDate = $data['reservation']['from'];
                        $endDate = $data['reservation']['to'];
                        // discounts between dates
                        $query->whereBetween('from', [$startDate, $endDate])
                            ->orWhereBetween('to', [$startDate, $endDate])
                            ->orWhere(function ($query) use ($startDate, $endDate) {
                                $query->where('from', '<=', $startDate)
                                    ->where('to', '>=', $endDate);
                            });
                    }
                }
            }]);

        if (isset($data['holiday_id'])) {
            $itemQuery = $itemQuery
                ->with(['holidays' => function ($query) use ($data) {
                    $query->where('holiday_id', $data['holiday_id']);
                }]);
        } elseif (isset($data['price_id'])) {
            $itemQuery = $itemQuery
                ->with(['prices' => fn($q) => $q->with('locales')->where('id', $data['price_id'])]);
        }

        return $itemQuery->find($data['item_id']);
    }

    /**
     * @param $orderLine :: the created or updated orderLine
     * @param $data
     * @param bool $create
     * @return void
     */
    public function processRelations(&$orderLine, &$data, bool $create = false)
    {
        $item = $this->getItemWithRelations($data);

        if (!isset($orderLine['subtotal_price']) || !isset($orderLine['total_price'])) {
            $this->setOrderLinePrices($orderLine);
        }

        $orderLine['data'] = [];

        // apply discounts -- take care of sorting, must be first
        $this->applyDiscounts($orderLine, $item);

        $this->applyAddons($orderLine, $item);

        $this->applyInsurance($orderLine, $item);

        // caching orderLine item data
        $orderLine['data'] += [
            "item" => $item,
            "user" => auth()->user(),
            'subtotal_price' => $orderLine['subtotal_price'],
            'total_price' => $orderLine['total_price'],
        ];

        $orderLine = tap(OrderLine::find($orderLine->id))->update([
            'subtotal_price' => $orderLine['subtotal_price'],
            'total_price' => $orderLine['total_price'],
            'data' => $orderLine['data']
        ]);

        if (isset($data['reservation']))
            $this->reservationRepository->set($item, $orderLine, $data['reservation']);

    }

    public function applyDiscounts(&$orderLine, $item)
    {
        // apply item discounts
        $discounts = $item->discounts ?? [];
        $discountAmount = 0;
        $itemPrice = $orderLine['total_price'];
        if (!count($discounts)) return;
        foreach ($discounts as &$discount) {
            $discountAmount += $discount->type == DiscountTypes::VALUE ? $discount->amount : $itemPrice * $discount->amount / 100;
        }
        $orderLine['data'] += ['discounts' => $item['discounts']];
        $orderLine['total_price'] = $itemPrice - $discountAmount;
    }

    public function applyAddons(&$orderLine, $item)
    {
        // orderLine addons data
        $addonsPrice = 0;
        $addons = $item['addons'] ?? [];
        foreach ($addons as &$addon) {
            $addonsPrice += $addon['price'];
        }
        $orderLine['data'] += ["addons" => $addons];
        $orderLine['total_price'] += $addonsPrice;
        $orderLine['subtotal_price'] += $addonsPrice;
    }

    public function applyInsurance(&$orderLine, $item)
    {
        if (isset($item->itemable->insurance)) {
            $insurance = isset($item->itemable) ? $item->itemable->insurance : 0;
            $orderLine['subtotal_price'] += $insurance;
            $orderLine['total_price'] += $insurance;
        }
    }

    public function createModel(array $data)
    {
        $data['user_id'] = auth('sanctum')->user()->id;
        $orderLine = $this->create($this->process($data));
        $this->processRelations($orderLine, $data, true);
        return $orderLine;
    }

    public function createManyOLs($orderId, array $orderLines): array
    {
        if (!isset($orderLines))
            abort(400, "No data detected");

        foreach ($orderLines as &$orderLine) {
            // todo:: check if no prices sent
            if (!isset($orderLine['price_id']) && !isset($orderLine['holiday_id']))
                continue; //  abort(400, "Wrong Data");
            $this->setOrderLinePrices($orderLine);
        }
        $createdOrderLines = [];
        foreach ($orderLines as &$ol) {
            $ol['order_id'] = $orderId;
            $createdOrderLines[] = $this->createModel($ol);
        }
        return $createdOrderLines;
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

    /**
     * Checks this price id for this item and return price
     * @param $itemId
     * @param $priceId
     * @return float
     */
    public function getItemPrice($itemId, $priceId)
    {
        $price = Price::find($priceId);
        if (!$price)
            abort(400, 'Price not found');
        $priceable = $price->priceable;
        // todo :: add sent price must equals the calculated
        if (!$priceable || get_class($priceable) !== Item::class || $itemId !== $priceable->id)
            abort(400, "Wrong Data");
        return $price->price;
    }

    /**
     * To get Item holidays and check intersections
     * Get intersected holiday & compare with current holiday id
     * Do nothing if not matches
     * @param $itemId
     * @param $reservation
     * @return array|false : return intersected holiday price
     */
    public function getMatchedHoliday($itemId, $reservation)
    {
        // prepare requested reservation period
        $reservationPeriod = Period::make(Carbon::parse($reservation['from']), Carbon::parse($reservation['to']));
        // get item holidays
        $item = Item::with('holidays')->find($itemId);
        $itemHolidays = $item->holidays;
        $matchedHoliday = null;
        // Check for intersection with the item holidays
        if (isset($item->holidays) && sizeof($itemHolidays) > 0) {
            foreach ($item->holidays as $itemHoliday) {
                $itemHolidayPeriod = Period::make($itemHoliday->from, $itemHoliday->to);
                $intersection = $reservationPeriod->overlap($itemHolidayPeriod);
                if ($intersection) {
                    $matchedHoliday = $itemHoliday;
                    break;
                }
            }
        }
        return $matchedHoliday ?? false;
    }

    public function setOrderLinePrices(&$orderLine)
    {
        // if reservation check holiday intersection
        if (isset($orderLine['reservation'])) {
            $matchedHoliday = $this->getMatchedHoliday($orderLine['item_id'], $orderLine['reservation']);
            if ($matchedHoliday) {
                if ($orderLine['holiday_id'] !== $matchedHoliday->id)
                    abort(400, "Timing matches holiday");
                $price = $matchedHoliday['price'];
            }
        }
        if (!isset($price))
            $price = $this->getItemPrice($orderLine['item_id'], $orderLine['price_id']);
        $orderLine['subtotal_price'] = $price;
        $orderLine['total_price'] = $price;
    }

}
