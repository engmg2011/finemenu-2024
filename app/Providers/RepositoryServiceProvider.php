<?php

namespace App\Providers;

use App\Repository\AreaRepositoryInterface;
use App\Repository\BookmarkRepositoryInterface;
use App\Repository\BranchRepositoryInterface;
use App\Repository\BusinessRepositoryInterface;
use App\Repository\CategoryRepositoryInterface;
use App\Repository\ContactRepositoryInterface;
use App\Repository\ContentRepositoryInterface;
use App\Repository\DeviceRepositoryInterface;
use App\Repository\DietPlanRepositoryInterface;
use App\Repository\DietPlanSubscriptionRepositoryInterface;
use App\Repository\DiscountRepositoryInteface;
use App\Repository\Eloquent\AreaRepository;
use App\Repository\Eloquent\BaseRepository;
use App\Repository\Eloquent\BookmarkRepository;
use App\Repository\Eloquent\BranchRepository;
use App\Repository\Eloquent\BusinessRepository;
use App\Repository\Eloquent\CategoryRepository;
use App\Repository\Eloquent\ContactRepository;
use App\Repository\Eloquent\DeviceRepository;
use App\Repository\Eloquent\DietPlanRepository;
use App\Repository\Eloquent\DietPlanSubscriptionRepository;
use App\Repository\Eloquent\DiscountRepository;
use App\Repository\Eloquent\EventRepository;
use App\Repository\Eloquent\FeatureOptionsRepository;
use App\Repository\Eloquent\FeatureRepository;
use App\Repository\Eloquent\HolidayRepository;
use App\Repository\Eloquent\InvoiceRepository;
use App\Repository\Eloquent\Itemable\CarProductRepository;
use App\Repository\Eloquent\Itemable\ChaletRepository;
use App\Repository\Eloquent\Itemable\SalonProductRepository;
use App\Repository\Eloquent\Itemable\SalonServiceRepository;
use App\Repository\Eloquent\ItemRepository;
use App\Repository\Eloquent\LocaleRepository;
use App\Repository\Eloquent\MediaRepository;
use App\Repository\Eloquent\MenuRepository;
use App\Repository\Eloquent\OrderLineRepository;
use App\Repository\Eloquent\OrderRepository;
use App\Repository\Eloquent\PermissionRepository;
use App\Repository\Eloquent\PriceRepository;
use App\Repository\Eloquent\ReservationRepository;
use App\Repository\Eloquent\SeatRepository;
use App\Repository\Eloquent\SettingRepository;
use App\Repository\Eloquent\UserRepository;
use App\Repository\EloquentRepositoryInterface;
use App\Repository\FeatureOptionsRepositoryInterface;
use App\Repository\FeatureRepositoryInterface;
use App\Repository\HolidayRepositoryInterface;
use App\Repository\InvoiceRepositoryInterface;
use App\Repository\ItemableInterfaces\CarProductRepositoryInterface;
use App\Repository\ItemableInterfaces\ChaletRepositoryInterface;
use App\Repository\ItemableInterfaces\SalonProductRepositoryInterface;
use App\Repository\ItemableInterfaces\SalonServiceRepositoryInterface;
use App\Repository\ItemRepositoryInterface;
use App\Repository\LocaleRepositoryInterface;
use App\Repository\MediaRepositoryInterface;
use App\Repository\MenuRepositoryInterface;
use App\Repository\OrderLineRepositoryInterface;
use App\Repository\OrderRepositoryInterface;
use App\Repository\PermissionRepositoryInterface;
use App\Repository\PriceRepositoryInterface;
use App\Repository\ReservationRepositoryInterface;
use App\Repository\SeatRepositoryInterface;
use App\Repository\SettingRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use Illuminate\Support\ServiceProvider;


class RepositoryServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->bind(EloquentRepositoryInterface::class, BaseRepository::class);
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(ContactRepositoryInterface::class, ContactRepository::class);
        $this->app->bind(DeviceRepositoryInterface::class, DeviceRepository::class);
        $this->app->bind(ContentRepositoryInterface::class, EventRepository::class);
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);
        $this->app->bind(LocaleRepositoryInterface::class, LocaleRepository::class);
        $this->app->bind(MediaRepositoryInterface::class, MediaRepository::class);
        $this->app->bind(OrderLineRepositoryInterface::class, OrderLineRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(PriceRepositoryInterface::class, PriceRepository::class);
        $this->app->bind(BusinessRepositoryInterface::class, BusinessRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(DietPlanRepositoryInterface::class, DietPlanRepository::class);
        $this->app->bind(DietPlanSubscriptionRepositoryInterface::class, DietPlanSubscriptionRepository::class);
        $this->app->bind(AreaRepositoryInterface::class, AreaRepository::class);
        $this->app->bind(SeatRepositoryInterface::class, SeatRepository::class);
        $this->app->bind(BranchRepositoryInterface::class, BranchRepository::class);
        $this->app->bind(MenuRepositoryInterface::class, MenuRepository::class);
        $this->app->bind(PermissionRepositoryInterface::class, PermissionRepository::class);
        $this->app->bind(BookmarkRepositoryInterface::class, BookmarkRepository::class);
        $this->app->bind(ReservationRepositoryInterface::class, ReservationRepository::class);
        $this->app->bind(InvoiceRepositoryInterface::class, InvoiceRepository::class);
        $this->app->bind(ChaletRepositoryInterface::class, ChaletRepository::class);
        $this->app->bind(HolidayRepositoryInterface::class, HolidayRepository::class);
        $this->app->bind(DiscountRepositoryInteface::class, DiscountRepository::class);
        $this->app->bind(SalonServiceRepositoryInterface::class, SalonServiceRepository::class);
        $this->app->bind(SalonProductRepositoryInterface::class, SalonProductRepository::class);
        $this->app->bind(FeatureRepositoryInterface::class, FeatureRepository::class);
        $this->app->bind(FeatureOptionsRepositoryInterface::class, FeatureOptionsRepository::class);
        $this->app->bind(CarProductRepositoryInterface::class, CarProductRepository::class);

    }

    /**
     * Bootstrap services.
     *
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
