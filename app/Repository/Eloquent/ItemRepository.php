<?php

namespace App\Repository\Eloquent;


use App\Actions\AddonAction;
use App\Actions\MediaAction;
use App\Constants\BusinessTypes;
use App\Constants\CategoryTypes;
use App\Models\Branch;
use App\Models\Category;
use App\Models\Item;
use App\Repository\ChaletRepositoryInterface;
use App\Repository\DiscountRepositoryInteface;
use App\Repository\ItemInterfaces\CarRepositoryInterface;
use App\Repository\ItemRepositoryInterface;
use App\Repository\SalonProductRepositoryInterface;
use App\Repository\SalonServiceRepositoryInterface;
use DB;
use Illuminate\Database\Eloquent\Model;

class ItemRepository extends BaseRepository implements ItemRepositoryInterface
{

    public function __construct(Item                                    $model,
                                private MediaAction                     $mediaAction,
                                private LocaleRepository                $localeAction,
                                private PriceRepository                 $priceAction,
                                private AddonAction                     $addonAction,
                                private DiscountRepositoryInteface      $discountRepository,
                                private ChaletRepositoryInterface       $chaletRepository,
                                private SalonServiceRepositoryInterface $salonServiceRepository,
                                private SalonProductRepositoryInterface $salonProductRepository,
                                private CarRepositoryInterface          $carRepository)
    {
        parent::__construct($model);
    }

    public function process(array $data)
    {
        return array_only($data, ['category_id', 'user_id', 'sort', 'hide', 'disable_ordering', 'itemable_id', 'itemable_type']);
    }

    public static array $modelRelations = ['locales', 'media', 'prices.locales', 'addons.locales',
        'discounts.locales', 'itemable.features.locales'];


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

    public function search($businessId, $branchId, $conditions = null)
    {
        $menuId = Branch::select('menu_id')->find($branchId)->menu_id;
        $categoriesIds = Category::where('menu_id', $menuId)->pluck('id')->toArray();
        $searchTerm = request('search');
        return $this->model::with(self::$modelRelations)
            ->whereIn('category_id', $categoriesIds)
            ->whereHas('locales', function ($query) use ($searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('description', 'like', '%' . $searchTerm . '%');
                });
            })
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
            $this->discountRepository->set($model, $data['discounts']);
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

        // Create itemable model
        if (in_array($businessType, [BusinessTypes::CHALET, BusinessTypes::SALON])) {
            $itemableData = ($data['itemable'] ?? []) + ['item_id' => $item->id];
            switch ($businessType) {
                case BusinessTypes::CHALET:
                    $itemable = $this->chaletRepository->createModel($itemableData);
                    break;
                case BusinessTypes::CARS:
                    $itemable = $this->carRepository->createModel($itemableData);
                    break;
                case BusinessTypes::SALON:
                    $category = Category::find($data['category_id']);
                    if ($category->type === CategoryTypes::SERVICE)
                        $itemable = $this->salonServiceRepository->createModel($itemableData);
                    else
                        $itemable = $this->salonProductRepository->createModel($itemableData);
                    break;
                default:
                    break;
            }
            $item->itemable()->associate($itemable);
            $item->save();
        }

        return Item::with(self::$modelRelations)->find($item->id);
    }

    public function update($id, array $data): Model
    {
        $businessType = '';
        // TODO:: need better solution not depends on categoryId
        if (isset($data['category_id']))
            $businessType = $this->getBusinessType($data['category_id'], $data);
        $model = tap($this->model->find($id))
            ->update($this->process($data));
        $this->relations($model, $data);

        // Create itemable model
        if (isset($data['itemable']) && in_array($businessType, [BusinessTypes::CHALET, BusinessTypes::SALON])) {
            $data['itemable']['item_id'] = $id;
            $itemableData = $data['itemable'];
            switch ($businessType) {
                case BusinessTypes::CHALET:
                    $itemable = $this->chaletRepository->set($itemableData);
                    break;
                case BusinessTypes::CARS:
                    $itemable = $this->carRepository->set($itemableData);
                    break;
                case BusinessTypes::SALON:
                    $category = Category::find($data['category_id']);
                    if ($category->type === CategoryTypes::SERVICE)
                        $itemable = $this->salonServiceRepository->set($itemableData);
                    else
                        $itemable = $this->salonProductRepository->set($itemableData);
                    break;
                default:
                    break;
            }
            $model->itemable()->associate($itemable);
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

    public function listHolidays($businessId, $itemId)
    {
        return $this->model->with('holidays.locales')->find($itemId);
    }

    public function syncHolidays($businessId, $itemId)
    {
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
