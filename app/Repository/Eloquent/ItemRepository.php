<?php

namespace App\Repository\Eloquent;


use App\Actions\AddonAction;
use App\Actions\DiscountAction;
use App\Actions\MediaAction;
use App\Constants\BusinessTypes;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Item;
use App\Repository\ChaletRepositoryInterface;
use App\Repository\ItemRepositoryInterface;
use DB;
use Illuminate\Database\Eloquent\Model;

class ItemRepository extends BaseRepository implements ItemRepositoryInterface
{

    public function __construct(Item                              $model,
                                private MediaAction               $mediaAction,
                                private LocaleRepository          $localeAction,
                                private PriceRepository           $priceAction,
                                private AddonAction               $addonAction,
                                private DiscountAction            $discountAction,
                                private ChaletRepositoryInterface $chaletRepository)
    {
        parent::__construct($model);
    }

    public function process(array $data)
    {
        return array_only($data, ['category_id', 'user_id', 'sort', 'hide', 'disable_ordering', 'itemable_id', 'itemable_type']);
    }

    public static array $modelRelations = ['locales', 'media', 'prices.locales', 'addons.locales',
        'discounts.locales', 'itemable'];


    public function list()
    {
        return $this->model::with(self::$modelRelations)
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function listModel($businessId, $branchId, $conditions = null)
    {
        $menuId = Branch::select('menu_id')->find($branchId)->menu_id;
        $categoriesIds = Category::where('menu_id', $menuId)->pluck('id')->toArray();
        return $this->model::with(self::$modelRelations)
            ->whereIn('category_id', $categoriesIds)
            ->where(fn($q) => $conditions ? $q->where(...$conditions) : $q)
            ->orderByDesc('id')->paginate(request('per-page', 15));
    }

    public function relations($model, $data)
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

    public function getBusinessType($categoryId)
    {
        $category = Category::with('menu.business')->find($categoryId);
        return $category->menu->business->type;
    }

    public function create(array $data): Model
    {
        $businessType = '';
        if (isset($data['category_id']))
            $businessType = $this->getBusinessType($data['category_id']);
        $data['user_id'] = auth('sanctum')->user()->id;
        $item = $this->model->create($this->process($data));
        $this->relations($item, $data);
        if ($businessType === BusinessTypes::CHALET) {
            $chaletData = ($data['itemable'] ?? []) + ['item_id' => $item->id];
            $chalet = $this->chaletRepository->createModel($chaletData);
            $item->itemable()->associate($chalet);
            $item->save();
        }
        return Item::with(self::$modelRelations)->find($item->id);
    }

    public function update($id, array $data): Model
    {
        $businessType = '';
        if (isset($data['category_id']))
            $businessType = $this->getBusinessType($data['category_id'], $data);
        $model = tap($this->model->find($id))
            ->update($this->process($data));
        $this->relations($model, $data);
        if ($businessType === BusinessTypes::CHALET && isset($data['itemable'])) {
            $data['itemable']['item_id'] = $id;
            $chalet = $this->chaletRepository->set($data['itemable']);
            $model->itemable()->associate($chalet);
            $model->save();
        }
        return $this->model->with(self::$modelRelations)->find($model->id);
    }

    public function sort($data)
    {
        DB::transaction(function () use ($data) {
            foreach ($data['sortedIds'] as $index => $id) {
                Item::where('id', $id)->update(['sort' => $index + 1]);
            }
        });
        return true;
    }


    public function get(int $id)
    {
        return Item::with(self::$modelRelations)->find($id);
    }

    public function destroy($id): ?bool
    {
        $this->localeAction->deleteEntityLocales(Item::find($id));
        return $this->delete($id);
    }

    public function listHolidays($businessId ,$itemId){
        return $this->model->with('holidays.locales')->find($itemId);
    }

    public function syncHolidays($businessId ,$itemId){
        $request = request();
        $request->validate([
            'holidays' => 'required|array',
            'holidays.*.holidayId' => 'required|exists:holidays,id',
            'holidays.*.price' => 'required|numeric|min:0',
        ]);

        $holidaysData = collect($request->holidays)->mapWithKeys(function ($holiday) {
            return [$holiday['holidayId'] => ['price' => $holiday['price']]];
        })->toArray();

        $item = $this->model->find($itemId);
        $item->holidays()->sync($holidaysData);
        return ['message' => 'Holidays synced successfully.'];
    }
}
