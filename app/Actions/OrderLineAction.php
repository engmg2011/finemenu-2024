<?php


namespace App\Actions;


use App\Models\OrderLine;
use App\Models\Price;
use App\Repository\Eloquent\OrderLineRepository;
use App\Repository\Eloquent\PriceRepository;
use Illuminate\Database\Eloquent\Model;

class OrderLineAction
{

    public function __construct(private OrderLineRepository $repository,
                                private PriceRepository $priceAction,
                                private AddonAction $addonAction,
                                private DiscountAction $discountAction)
    {
    }

    public function process(array $data): array
    {
        $data['user_id'] = auth('api')->user()->id;
        return array_only($data, ['user_id','note','order_id','item_id', 'count', 'price_id']);
    }

    public function processRelations(&$orderLine, &$data)
    {
        if(isset($data['price_id'])){
            $priceData = Price::select(["price"])->find($data['price_id'])?->toArray();
            if($priceData)
                $this->priceAction->setPrices($orderLine, [$priceData]);
        }
        if (isset($data['addons']))
            $this->addonAction->set($orderLine, $data['addons']);
        if (isset($data['discounts']))
            $this->discountAction->set($orderLine, $data['discounts']);
    }

    public function create(array $data)
    {
        $orderLine = $this->repository->create($this->process($data));
        $this->processRelations($orderLine, $data);
        return $orderLine;
    }

    public function createMany($orderLines)
    {
        $createArr = [];
        foreach ($orderLines as $orderLine) {
            $createArr[] = $this->create($orderLine);
        }
        return $createArr;
    }

    public function update($id, array $data): Model
    {
        $orderLine = tap($this->repository->find($id))
            ->update($this->process($data));
        $this->processRelations($orderLine, $data);
        return $orderLine;
    }

    public function updateMany($orderLines)
    {
        $arr = [];
        foreach ($orderLines as &$orderLine){
            $arr = $this->repository->updateOrCreate(['id' => $orderLine['id']],$orderLine);
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
