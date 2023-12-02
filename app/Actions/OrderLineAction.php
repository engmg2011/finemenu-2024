<?php


namespace App\Actions;


use App\Models\Order;
use App\Models\OrderLine;
use App\Repository\Eloquent\OrderLineRepository;
use Illuminate\Database\Eloquent\Model;

class OrderLineAction
{

    public function __construct(private OrderLineRepository $repository,
                                private PriceAction $priceAction,
                                private AddonAction $addonAction,
                                private DiscountAction $discountAction)
    {
    }

    public function process(array $data): array
    {
        $data['user_id'] = auth('api')->user()->id;
        return array_only($data, ['user_id','note','order_id','item_id', 'count']);
    }

    public function create(array $data)
    {
        $orderLine = $this->repository->create($this->process($data));
        if (isset($data['prices']))
            $this->priceAction->setPrices($orderLine, $data['prices']);
        if (isset($data['addons']))
            $this->addonAction->set($orderLine, $data['addons']);
        if (isset($data['discounts']))
            $this->discountAction->set($orderLine, $data['discounts']);
        return $orderLine;
    }

    public function createMany($orderLines)
    {
        $processedArr = [];
        foreach ($orderLines as $orderLine)
            $processedArr[] = $this->process($orderLine);
        $this->repository->createMany($processedArr);
    }

    public function update($id, array $data): Model
    {
        $orderLine = tap($this->repository->find($id))
            ->update($this->process($data));
        if (isset($data['prices']))
            $this->priceAction->setPrices($orderLine, $data['prices']);
        if (isset($data['addons']))
            $this->addonAction->set($orderLine, $data['addons']);
        if (isset($data['discounts']))
            $this->discountAction->set($orderLine, $data['discounts']);
        return $orderLine;
    }

    public function updateMany($orderLines)
    {
        foreach ($orderLines as &$orderLine){
            $this->repository->updateOrCreate(['id' => $orderLine['id']],$orderLine);
        }
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
