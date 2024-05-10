<?php

namespace App\Repository\Eloquent;


use App\Actions\AddonAction;
use App\Actions\DiscountAction;
use App\Actions\MediaAction;
use App\Models\Item;
use App\Repository\ItemRepositoryInterface;
use Illuminate\Database\Eloquent\Model;

class ItemRepository extends BaseRepository implements ItemRepositoryInterface
{

    public function __construct( Item                  $model,
                                private MediaAction    $mediaAction,
                                private LocaleRepository   $localeAction,
                                private PriceRepository    $priceAction,
                                private AddonAction    $addonAction,
                                private DiscountAction $discountAction)
    {
        parent::__construct($model);
    }

    public function list()
    {
        return $this->model::with(['locales', 'media', 'prices', 'addons', 'discounts'])
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }


    public static array $itemRelations = ['locales', 'media', 'prices.locales','discounts.locales', 'addons.locales'];


    public function process(Array $data) {
        return array_only( $data , ['category_id', 'user_id', 'sort']);
    }

    public function relations($model , $data)
    {
        if (isset($data['locales']))
            $this->localeAction->setLocales($model, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        if (isset($data['addons']))
            $this->addonAction->set($model, $data['addons']);
        if (isset($data['prices']))
            $this->priceAction->setPrices($model, $data['prices']);
        if (isset($data['discounts']))
            $this->discountAction->set($model, $data['discounts']);
    }

    public function create(Array $data): Model
    {
        $data['user_id'] = auth('api')->user()->id;
        $item = $this->model->create($this->process($data));
        $this->relations($item, $data);
        return $item;
    }

    public function update($id, array $data): Model
    {
        $model = tap($this->model->find($id))
            ->update($this->process($data));
        $this->relations($model, $data);
        return $this->model->with(ItemRepository::$itemRelations)->find($model->id);
    }

    public function sort($data)
    {
        $sort = 1 ;
        foreach ($data['sortedIds'] as $id ){
            $this->model->whereId($id)->update(['sort'=>$sort, 'category_id' => $data['categoryId']]);
            $sort++;
        }
        return true;
    }


    public function get(int $id)
    {
        return Item::with(ItemRepository::$itemRelations)->find($id);
    }

    public function destroy($id): ?bool
    {
        return $this->delete($id);
    }

}
