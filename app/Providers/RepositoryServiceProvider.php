<?php

namespace App\Providers;

use App\Repository\CategoryRepositoryInterface;
use App\Repository\ContactRepositoryInterface;
use App\Repository\DeviceRepositoryInterface;
use App\Repository\DietPlanSubscriptionRepositoryInterface;
use App\Repository\Eloquent\CategoryRepository;
use App\Repository\Eloquent\ContactRepository;
use App\Repository\Eloquent\DeviceRepository;
use App\Repository\Eloquent\DietPlanSubscriptionRepository;
use App\Repository\Eloquent\EventRepository;
use App\Repository\Eloquent\HotelRepository;
use App\Repository\Eloquent\ItemRepository;
use App\Repository\Eloquent\LocaleRepository;
use App\Repository\Eloquent\MediaRepository;
use App\Repository\Eloquent\OrderLineRepository;
use App\Repository\Eloquent\OrderRepository;
use App\Repository\Eloquent\DietPlanRepository;
use App\Repository\Eloquent\PriceRepository;
use App\Repository\Eloquent\RestaurantRepository;
use App\Repository\Eloquent\SettingRepository;
use App\Repository\ContentRepositoryInterface;
use App\Repository\HotelRepositoryInteface;
use App\Repository\ItemRepositoryInterface;
use App\Repository\LocaleRepositoryInterface;
use App\Repository\MediaRepositoryInterface;
use App\Repository\OrderLineRepositoryInterface;
use App\Repository\OrderRepositoryInterface;
use App\Repository\DietPlanRepositoryInterface;
use App\Repository\PriceRepositoryInterface;
use App\Repository\RestaurantRepositoryInterface;
use App\Repository\SettingRepositoryInterface;
use Illuminate\Support\ServiceProvider;

use App\Repository\EloquentRepositoryInterface;
use App\Repository\UserRepositoryInterface;
use App\Repository\Eloquent\UserRepository;
use App\Repository\Eloquent\BaseRepository;


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
        $this->app->bind(HotelRepositoryInteface::class, HotelRepository::class);
        $this->app->bind(ItemRepositoryInterface::class, ItemRepository::class);
        $this->app->bind(LocaleRepositoryInterface::class, LocaleRepository::class);
        $this->app->bind(MediaRepositoryInterface::class, MediaRepository::class);
        $this->app->bind(OrderLineRepositoryInterface::class, OrderLineRepository::class);
        $this->app->bind(OrderRepositoryInterface::class, OrderRepository::class);
        $this->app->bind(PriceRepositoryInterface::class, PriceRepository::class);
        $this->app->bind(RestaurantRepositoryInterface::class, RestaurantRepository::class);
        $this->app->bind(SettingRepositoryInterface::class, SettingRepository::class);
        $this->app->bind(DietPlanRepositoryInterface::class, DietPlanRepository::class);
        $this->app->bind(DietPlanSubscriptionRepositoryInterface::class, DietPlanSubscriptionRepository::class);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
