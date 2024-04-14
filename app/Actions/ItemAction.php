<?php


namespace App\Actions;

use App\Models\Item;
use App\Repository\Eloquent\ItemRepository;
use Illuminate\Database\Eloquent\Model;
use function array_only;

class ItemAction
{

    public static array $itemRelations = ['locales', 'media', 'prices.locales','discounts.locales', 'addons.locales'];

    public function __construct(
        private ItemRepository $repository,
        private MediaAction $mediaAction,
        private LocaleAction $localeAction,
        private PriceAction $priceAction,
        private AddonAction $addonAction,
        private DiscountAction $discountAction
    ) {

    }

    public function process(Array $data) {
        return array_only( $data , ['category_id', 'user_id', 'sort']);
    }

    public function create(Array $data) {
        $data['user_id'] = auth('api')->user()->id;
        $item = $this->repository->create($this->process($data));
        $this->localeAction->createLocale($item, $data['locales']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($item, $data['media']);
        if (isset($data['addons']))
            $this->addonAction->set($item, $data['addons']);
        if (isset($data['discounts']))
            $this->discountAction->set($item, $data['discounts']);
        return $item;
    }

    public function update($id, array $data)
    {
        $model = tap($this->repository->find($id))
            ->update($this->process($data));
        $this->localeAction->updateLocales($model, $data['locales']);
        if (isset($data['prices']))
            $this->priceAction->setPrices($model, $data['prices']);
        if (isset($data['media']))
            $this->mediaAction->setMedia($model, $data['media']);
        if (isset($data['addons']))
            $this->addonAction->set($model, $data['addons']);
        if (isset($data['discounts']))
            $this->discountAction->set($model, $data['discounts']);
        return $this->repository->with(ItemAction::$itemRelations)->find($model->id);
    }

    public function sort($data)
    {
        $sort = 1 ;
        foreach ($data['sortedIds'] as $id ){
            $this->repository->whereId($id)->update(['sort'=>$sort, 'category_id' => $data['categoryId']]);
            $sort++;
        }
        return true;
    }

    /**
     * @return mixed
     */
    public function list()
    {
        return $this->repository->list();
    }

    public function get(int $id)
    {
        return Item::with(ItemAction::$itemRelations)->find($id);
    }

    public function destroy($id): ?bool
    {
        return $this->repository->delete($id);
    }

}
