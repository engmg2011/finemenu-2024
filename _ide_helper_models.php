<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\Addon
 *
 * @property int $id
 * @property int $addonable_id
 * @property string $addonable_type
 * @property float|null $price
 * @property int $multiple
 * @property int|null $max
 * @property int $user_id
 * @property int|null $parent_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Addon> $children
 * @property-read int|null $children_count
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @method static Builder|Addon newModelQuery()
 * @method static Builder|Addon newQuery()
 * @method static Builder|Addon query()
 * @method static Builder|Addon whereAddonableId($value)
 * @method static Builder|Addon whereAddonableType($value)
 * @method static Builder|Addon whereCreatedAt($value)
 * @method static Builder|Addon whereId($value)
 * @method static Builder|Addon whereMax($value)
 * @method static Builder|Addon whereMultiple($value)
 * @method static Builder|Addon whereParentId($value)
 * @method static Builder|Addon wherePrice($value)
 * @method static Builder|Addon whereUpdatedAt($value)
 * @method static Builder|Addon whereUserId($value)
 * @mixin Eloquent
 * @property-read \App\Models\Media|null $featuredImage
 */
	class Addon extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Area
 *
 * @property int $id
 * @property int $business_id
 * @property int $branch_id
 * @property int $sort
 * @property-read Collection<int, Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, Seat> $tables
 * @property-read int|null $tables_count
 * @method static Builder|Area newModelQuery()
 * @method static Builder|Area newQuery()
 * @method static Builder|Area query()
 * @method static Builder|Area whereBranchId($value)
 * @method static Builder|Area whereBusinessId($value)
 * @method static Builder|Area whereId($value)
 * @method static Builder|Area whereSort($value)
 * @mixin Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Seat> $seats
 * @property-read int|null $seats_count
 */
	class Area extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Audit
 *
 * @property int $id
 * @property string $service_type
 * @property int $service_id
 * @property string $message
 * @property array $request
 * @property array|null $data
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int $user_id
 * @property int|null $business_id
 * @property int|null $branch_id
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder|Audit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Audit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Audit query()
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereMessage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereRequest($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereServiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereServiceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Audit whereUserId($value)
 */
	class Audit extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Bookmark
 *
 * @property int $id
 * @property int $item_id
 * @property int $user_id
 * @property int $branch_id
 * @property int $business_id
 * @property-read \App\Models\Item $item
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Bookmark newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bookmark newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Bookmark query()
 * @method static \Illuminate\Database\Eloquent\Builder|Bookmark whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bookmark whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bookmark whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bookmark whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Bookmark whereUserId($value)
 * @mixin \Eloquent
 */
	class Bookmark extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Branch
 *
 * @property int $id
 * @property int|null $business_id
 * @property int|null $menu_id
 * @property int $sort
 * @property string|null $slug
 * @property-read \App\Models\Business|null $business
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Area> $areas
 * @property-read int|null $areas_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Menu|null $menu
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Setting> $settings
 * @property-read int|null $settings_count
 * @method static \Illuminate\Database\Eloquent\Builder|Branch newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Branch newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Branch query()
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereMenuId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Branch whereSort($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Media|null $featuredImage
 */
	class Branch extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Business
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $name
 * @property int $user_id
 * @property string|null $passcode
 * @property string|null $slug
 * @property string|null $type
 * @property int|null $creator_id
 * @property-read Collection<int, \App\Models\Branch> $branches
 * @property-read int|null $branches_count
 * @property-read Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @property-read Collection<int, \App\Models\Contact> $contacts
 * @property-read int|null $contacts_count
 * @property-read Collection<int, \App\Models\Content> $contents
 * @property-read int|null $contents_count
 * @property-read Collection<int, \App\Models\Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read Collection<int, \App\Models\Menu> $menus
 * @property-read int|null $menus_count
 * @property-read Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, \App\Models\Setting> $settings
 * @property-read int|null $settings_count
 * @property-read \App\Models\User $user
 * @method static Builder|Business newModelQuery()
 * @method static Builder|Business newQuery()
 * @method static Builder|Business query()
 * @method static Builder|Business whereCreatedAt($value)
 * @method static Builder|Business whereCreatorId($value)
 * @method static Builder|Business whereId($value)
 * @method static Builder|Business whereName($value)
 * @method static Builder|Business wherePasscode($value)
 * @method static Builder|Business whereSlug($value)
 * @method static Builder|Business whereType($value)
 * @method static Builder|Business whereUpdatedAt($value)
 * @method static Builder|Business whereUserId($value)
 * @mixin Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Configuration> $configurations
 * @property-read int|null $configurations_count
 * @property-read \App\Models\Media|null $featuredImage
 */
	class Business extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Category
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $parent_id
 * @property int $user_id
 * @property int|null $sort
 * @property int|null $menu_id
 * @property-read \App\Models\Business|null $business
 * @property-read Collection<int, Category> $children
 * @property-read int|null $children_count
 * @property-read Collection<int, \App\Models\Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read Collection<int, \App\Models\Item> $items
 * @property-read int|null $items_count
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\Menu|null $menu
 * @property-read Collection<int, \App\Models\Setting> $settings
 * @property-read int|null $settings_count
 * @property-read \App\Models\User $user
 * @method static Builder|Category newModelQuery()
 * @method static Builder|Category newQuery()
 * @method static Builder|Category query()
 * @method static Builder|Category whereCreatedAt($value)
 * @method static Builder|Category whereId($value)
 * @method static Builder|Category whereMenuId($value)
 * @method static Builder|Category whereParentId($value)
 * @method static Builder|Category whereSort($value)
 * @method static Builder|Category whereUpdatedAt($value)
 * @method static Builder|Category whereUserId($value)
 * @mixin Eloquent
 * @property string|null $type
 * @property-read \App\Models\Media|null $featuredImage
 * @method static \Illuminate\Database\Eloquent\Builder|Category whereType($value)
 */
	class Category extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Configuration
 *
 * @property int $id
 * @property string $configurable_type
 * @property int $configurable_id
 * @property string $key
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $configurable
 * @method static \Illuminate\Database\Eloquent\Builder|Configuration newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Configuration newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Configuration query()
 * @method static \Illuminate\Database\Eloquent\Builder|Configuration whereConfigurableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Configuration whereConfigurableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Configuration whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Configuration whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Configuration whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Configuration whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Configuration whereValue($value)
 */
	class Configuration extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Contact
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $media
 * @property string $value
 * @property string $contactable_type
 * @property int $contactable_id
 * @property-read Model|\Eloquent $contactable
 * @method static Builder|Contact newModelQuery()
 * @method static Builder|Contact newQuery()
 * @method static Builder|Contact query()
 * @method static Builder|Contact whereContactableId($value)
 * @method static Builder|Contact whereContactableType($value)
 * @method static Builder|Contact whereCreatedAt($value)
 * @method static Builder|Contact whereId($value)
 * @method static Builder|Contact whereMedia($value)
 * @method static Builder|Contact whereUpdatedAt($value)
 * @method static Builder|Contact whereValue($value)
 * @mixin Eloquent
 * @property string $key
 * @method static \Illuminate\Database\Eloquent\Builder|Contact whereKey($value)
 */
	class Contact extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Content
 *
 * @property int $id
 * @property int $user_id
 * @property int|null $parent_id
 * @property string $contentable_type
 * @property int $contentable_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, Content> $children
 * @property-read int|null $children_count
 * @property-read Model|\Eloquent $contentable
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @method static Builder|Content newModelQuery()
 * @method static Builder|Content newQuery()
 * @method static Builder|Content query()
 * @method static Builder|Content whereContentableId($value)
 * @method static Builder|Content whereContentableType($value)
 * @method static Builder|Content whereCreatedAt($value)
 * @method static Builder|Content whereId($value)
 * @method static Builder|Content whereParentId($value)
 * @method static Builder|Content whereUpdatedAt($value)
 * @method static Builder|Content whereUserId($value)
 * @mixin Eloquent
 */
	class Content extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Device
 *
 * @property int $id
 * @property string $device_name
 * @property string|null $token_id
 * @property string|null $onesignal_token
 * @property mixed|null $info
 * @property string $last_active
 * @property string|null $os
 * @property mixed|null $versions
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Models\User $user
 * @method static Builder|Device newModelQuery()
 * @method static Builder|Device newQuery()
 * @method static Builder|Device query()
 * @method static Builder|Device whereCreatedAt($value)
 * @method static Builder|Device whereDeviceName($value)
 * @method static Builder|Device whereId($value)
 * @method static Builder|Device whereInfo($value)
 * @method static Builder|Device whereLastActive($value)
 * @method static Builder|Device whereOnesignalToken($value)
 * @method static Builder|Device whereOs($value)
 * @method static Builder|Device whereTokenId($value)
 * @method static Builder|Device whereUpdatedAt($value)
 * @method static Builder|Device whereUserId($value)
 * @method static Builder|Device whereVersions($value)
 * @mixin Eloquent
 */
	class Device extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DietPlan
 *
 * @property int $id
 * @property int $business_id
 * @property int $user_id
 * @property int|null $category_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read Collection<int, \App\Models\Item> $items
 * @property-read int|null $items_count
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read Collection<int, \App\Models\Price> $prices
 * @property-read int|null $prices_count
 * @method static Builder|DietPlan newModelQuery()
 * @method static Builder|DietPlan newQuery()
 * @method static Builder|DietPlan query()
 * @method static Builder|DietPlan whereBusinessId($value)
 * @method static Builder|DietPlan whereCategoryId($value)
 * @method static Builder|DietPlan whereCreatedAt($value)
 * @method static Builder|DietPlan whereId($value)
 * @method static Builder|DietPlan whereUpdatedAt($value)
 * @method static Builder|DietPlan whereUserId($value)
 * @mixin Eloquent
 * @property-read \App\Models\Media|null $featuredImage
 */
	class DietPlan extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\DietPlanSubscription
 *
 * @property int $id
 * @property int|null $diet_plan_id
 * @property int|null $creator_id
 * @property int|null $user_id
 * @property int $business_id
 * @property string $status
 * @property string $payment_status
 * @property array $selected_meals
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\DietPlan|null $dietPlan
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read \App\Models\User|null $user
 * @method static Builder|DietPlanSubscription newModelQuery()
 * @method static Builder|DietPlanSubscription newQuery()
 * @method static Builder|DietPlanSubscription query()
 * @method static Builder|DietPlanSubscription whereBusinessId($value)
 * @method static Builder|DietPlanSubscription whereCreatedAt($value)
 * @method static Builder|DietPlanSubscription whereCreatorId($value)
 * @method static Builder|DietPlanSubscription whereDietPlanId($value)
 * @method static Builder|DietPlanSubscription whereId($value)
 * @method static Builder|DietPlanSubscription wherePaymentStatus($value)
 * @method static Builder|DietPlanSubscription whereSelectedMeals($value)
 * @method static Builder|DietPlanSubscription whereStatus($value)
 * @method static Builder|DietPlanSubscription whereUpdatedAt($value)
 * @method static Builder|DietPlanSubscription whereUserId($value)
 * @mixin Eloquent
 */
	class DietPlanSubscription extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Discount
 *
 * @property int $id
 * @property int $discountable_id
 * @property string $discountable_type
 * @property float $amount
 * @property string $type
 * @property string|null $from
 * @property string|null $to
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @method static Builder|Discount newModelQuery()
 * @method static Builder|Discount newQuery()
 * @method static Builder|Discount query()
 * @method static Builder|Discount whereAmount($value)
 * @method static Builder|Discount whereCreatedAt($value)
 * @method static Builder|Discount whereDiscountableId($value)
 * @method static Builder|Discount whereDiscountableType($value)
 * @method static Builder|Discount whereFrom($value)
 * @method static Builder|Discount whereId($value)
 * @method static Builder|Discount whereTo($value)
 * @method static Builder|Discount whereType($value)
 * @method static Builder|Discount whereUpdatedAt($value)
 * @method static Builder|Discount whereUserId($value)
 * @mixin Eloquent
 */
	class Discount extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Event
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon $start
 * @property Carbon $end
 * @property string $eventable_type
 * @property int $eventable_id
 * @property int $user_id
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read Collection<int, \App\Models\Setting> $settings
 * @property-read int|null $settings_count
 * @property-read \App\Models\User $user
 * @method static Builder|Event newModelQuery()
 * @method static Builder|Event newQuery()
 * @method static Builder|Event query()
 * @method static Builder|Event whereCreatedAt($value)
 * @method static Builder|Event whereEnd($value)
 * @method static Builder|Event whereEventableId($value)
 * @method static Builder|Event whereEventableType($value)
 * @method static Builder|Event whereId($value)
 * @method static Builder|Event whereStart($value)
 * @method static Builder|Event whereUpdatedAt($value)
 * @method static Builder|Event whereUserId($value)
 * @mixin Eloquent
 * @property-read \App\Models\Media|null $featuredImage
 */
	class Event extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Featurable
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Featurable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Featurable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Featurable query()
 */
	class Featurable extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Feature
 *
 * @property int $id
 * @property string $key
 * @property string $type
 * @property string|null $itemable_type
 * @property string|null $icon
 * @property string|null $color
 * @property int|null $sort
 * @property int|null $category_id
 * @property-read \App\Models\Category|null $category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FeatureOptions> $feature_options
 * @property-read int|null $feature_options_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Items\SalonProduct> $salonProducts
 * @property-read int|null $salon_products_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Items\SalonService> $salonServices
 * @property-read int|null $salon_services_count
 * @method static \Illuminate\Database\Eloquent\Builder|Feature newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature query()
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereColor($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereIcon($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereIconFontType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereItemableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Feature whereType($value)
 */
	class Feature extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\FeatureOptions
 *
 * @property int $id
 * @property int $feature_id
 * @property int $sort
 * @property-read \App\Models\Feature $feature
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureOptions newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureOptions newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureOptions query()
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureOptions whereFeatureId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureOptions whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FeatureOptions whereSort($value)
 */
	class FeatureOptions extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Holiday
 *
 * @property int $id
 * @property string $from
 * @property string $to
 * @property int $user_id
 * @property int|null $business_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $price
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Item> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday query()
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Holiday whereUserId($value)
 */
	class Holiday extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Hotel
 *
 * @property-read Collection<int, \App\Models\Contact> $contacts
 * @property-read int|null $contacts_count
 * @property-read Collection<int, \App\Models\Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, \App\Models\Setting> $settings
 * @property-read int|null $settings_count
 * @property-read \App\Models\User|null $user
 * @method static Builder|Hotel newModelQuery()
 * @method static Builder|Hotel newQuery()
 * @method static Builder|Hotel query()
 * @mixin Eloquent
 * @property-read \App\Models\Media|null $featuredImage
 */
	class Hotel extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\InitRegister
 *
 * @property int $id
 * @property string|null $phone
 * @property string|null $email
 * @property string $code
 * @property int $tries_count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|InitRegister newModelQuery()
 * @method static Builder|InitRegister newQuery()
 * @method static Builder|InitRegister query()
 * @method static Builder|InitRegister whereCode($value)
 * @method static Builder|InitRegister whereCreatedAt($value)
 * @method static Builder|InitRegister whereEmail($value)
 * @method static Builder|InitRegister whereId($value)
 * @method static Builder|InitRegister wherePhone($value)
 * @method static Builder|InitRegister whereTriesCount($value)
 * @method static Builder|InitRegister whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class InitRegister extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Invoice
 *
 * @property int $id
 * @property float $amount
 * @property array|null $data
 * @property string|null $external_link
 * @property string|null $reference_id
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string $type
 * @property string $status
 * @property string|null $status_changed_at
 * @property string $payment_type
 * @property int|null $reservation_id
 * @property int|null $order_id
 * @property int|null $order_line_id
 * @property int|null $invoice_by_id
 * @property int|null $invoice_for_id
 * @property int|null $business_id
 * @property int|null $branch_id
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\Business|null $business
 * @property-read \App\Models\User|null $byUser
 * @property-read \App\Models\User|null $forUser
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\Reservation|null $reservation
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice query()
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereExternalLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereInvoiceById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereInvoiceForId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereOrderLineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice wherePaymentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereReferenceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereReservationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereStatusChangedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property \Illuminate\Support\Carbon|null $paid_at
 * @property \Illuminate\Support\Carbon|null $due_at
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice whereDueAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Invoice wherePaidAt($value)
 */
	class Invoice extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\IpTries
 *
 * @property int $id
 * @property string $ip
 * @property int $tries
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @method static Builder|IpTries newModelQuery()
 * @method static Builder|IpTries newQuery()
 * @method static Builder|IpTries query()
 * @method static Builder|IpTries whereCreatedAt($value)
 * @method static Builder|IpTries whereId($value)
 * @method static Builder|IpTries whereIp($value)
 * @method static Builder|IpTries whereTries($value)
 * @method static Builder|IpTries whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class IpTries extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Item
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $category_id
 * @property int $user_id
 * @property int|null $sort
 * @property bool $hide
 * @property bool $disable_ordering
 * @property int|null $itemable_id
 * @property string|null $itemable_type
 * @property-read Collection<int, \App\Models\Addon> $addons
 * @property-read int|null $addons_count
 * @property-read \App\Models\Category|null $category
 * @property-read Collection<int, \App\Models\Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read Model|\Eloquent $itemable
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read Collection<int, \App\Models\DietPlan> $plans
 * @property-read int|null $plans_count
 * @property-read Collection<int, \App\Models\Price> $prices
 * @property-read int|null $prices_count
 * @property-read Collection<int, \App\Models\Reservation> $reservations
 * @property-read int|null $reservations_count
 * @property-read Collection<int, \App\Models\Setting> $settings
 * @property-read int|null $settings_count
 * @property-read \App\Models\User $user
 * @method static Builder|Item newModelQuery()
 * @method static Builder|Item newQuery()
 * @method static Builder|Item query()
 * @method static Builder|Item whereCategoryId($value)
 * @method static Builder|Item whereCreatedAt($value)
 * @method static Builder|Item whereDisableOrdering($value)
 * @method static Builder|Item whereHide($value)
 * @method static Builder|Item whereId($value)
 * @method static Builder|Item whereItemableId($value)
 * @method static Builder|Item whereItemableType($value)
 * @method static Builder|Item whereSort($value)
 * @method static Builder|Item whereUpdatedAt($value)
 * @method static Builder|Item whereUserId($value)
 * @mixin Eloquent
 * @property-read \App\Models\Media|null $featuredImage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Holiday> $holidays
 * @property-read int|null $holidays_count
 */
	class Item extends \Eloquent {}
}

namespace App\Models\Items{
/**
 * App\Models\Items\Chalet
 *
 * @property int $id
 * @property int|null $insurance
 * @property float|null $latitude
 * @property float|null $longitude
 * @property array|null $address
 * @property string|null $frontage
 * @property int $bedrooms
 * @property int $item_id
 * @property int|null $owner_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Item|null $item
 * @method static Builder|Chalet newModelQuery()
 * @method static Builder|Chalet newQuery()
 * @method static Builder|Chalet query()
 * @method static Builder|Chalet whereAddress($value)
 * @method static Builder|Chalet whereBedrooms($value)
 * @method static Builder|Chalet whereCreatedAt($value)
 * @method static Builder|Chalet whereFrontage($value)
 * @method static Builder|Chalet whereId($value)
 * @method static Builder|Chalet whereInsurance($value)
 * @method static Builder|Chalet whereItemId($value)
 * @method static Builder|Chalet whereLatitude($value)
 * @method static Builder|Chalet whereLongitude($value)
 * @method static Builder|Chalet whereOwnerId($value)
 * @method static Builder|Chalet whereUpdatedAt($value)
 * @mixin Eloquent
 * @property int $amount
 * @property int $units
 * @property array|null $unit_names
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Feature> $features
 * @property-read int|null $features_count
 * @property-read mixed $features_data
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet whereUnitNames($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Chalet whereUnits($value)
 */
	class Chalet extends \Eloquent {}
}

namespace App\Models\Items{
/**
 * App\Models\Items\SalonProduct
 *
 * @property int $id
 * @property int $amount
 * @property int $item_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Feature> $features
 * @property-read int|null $features_count
 * @property-read mixed $features_data
 * @property-read \App\Models\Item|null $item
 * @method static \Illuminate\Database\Eloquent\Builder|SalonProduct newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalonProduct newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalonProduct query()
 * @method static \Illuminate\Database\Eloquent\Builder|SalonProduct whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalonProduct whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalonProduct whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalonProduct whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalonProduct whereUpdatedAt($value)
 */
	class SalonProduct extends \Eloquent {}
}

namespace App\Models\Items{
/**
 * App\Models\Items\SalonService
 *
 * @property int $id
 * @property int|null $duration
 * @property array|null $provider_employee_ids
 * @property int $item_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Feature> $features
 * @property-read int|null $features_count
 * @property-read mixed $features_data
 * @property-read \App\Models\Item|null $item
 * @method static \Illuminate\Database\Eloquent\Builder|SalonService newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalonService newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SalonService query()
 * @method static \Illuminate\Database\Eloquent\Builder|SalonService whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalonService whereDuration($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalonService whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalonService whereItemId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalonService whereProviderEmployeeIds($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SalonService whereUpdatedAt($value)
 */
	class SalonService extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Locales
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string|null $name
 * @property string|null $description
 * @property string $locale
 * @property string $localizable_type
 * @property int $localizable_id
 * @property-read Model|\Eloquent $localizable
 * @method static Builder|Locales newModelQuery()
 * @method static Builder|Locales newQuery()
 * @method static Builder|Locales query()
 * @method static Builder|Locales whereCreatedAt($value)
 * @method static Builder|Locales whereDescription($value)
 * @method static Builder|Locales whereId($value)
 * @method static Builder|Locales whereLocale($value)
 * @method static Builder|Locales whereLocalizableId($value)
 * @method static Builder|Locales whereLocalizableType($value)
 * @method static Builder|Locales whereName($value)
 * @method static Builder|Locales whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class Locales extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\LoginSession
 *
 * @property int $id
 * @property string $login_session
 * @property string $valid_until
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession query()
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereLoginSession($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|LoginSession whereValidUntil($value)
 * @mixin \Eloquent
 */
	class LoginSession extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Media
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $src
 * @property string $type
 * @property string $mediable_type
 * @property int $mediable_id
 * @property int $user_id
 * @property string|null $slug
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Model|\Eloquent $mediable
 * @property-read \App\Models\User $user
 * @method static Builder|Media newModelQuery()
 * @method static Builder|Media newQuery()
 * @method static Builder|Media query()
 * @method static Builder|Media whereCreatedAt($value)
 * @method static Builder|Media whereId($value)
 * @method static Builder|Media whereMediableId($value)
 * @method static Builder|Media whereMediableType($value)
 * @method static Builder|Media whereSlug($value)
 * @method static Builder|Media whereSrc($value)
 * @method static Builder|Media whereType($value)
 * @method static Builder|Media whereUpdatedAt($value)
 * @method static Builder|Media whereUserId($value)
 * @mixin Eloquent
 * @property int $sort
 * @method static \Illuminate\Database\Eloquent\Builder|Media whereSort($value)
 */
	class Media extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Menu
 *
 * @property int $id
 * @property string $slug
 * @property string|null $type
 * @property int $business_id
 * @property int $user_id
 * @property int $sort
 * @property-read \App\Models\Business $business
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Setting> $settings
 * @property-read int|null $settings_count
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu query()
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereSort($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Menu whereUserId($value)
 * @mixin \Eloquent
 * @property-read \App\Models\Media|null $featuredImage
 */
	class Menu extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Order
 *
 * @property int $id
 * @property string|null $note
 * @property string|null $scheduled_at
 * @property int $user_id
 * @property string $orderable_type
 * @property int $orderable_id
 * @property string|null $status
 * @property bool $paid
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property int|null $device_id
 * @property array|null $delivery_address
 * @property float $total_price
 * @property float $subtotal_price
 * @property-read \App\Models\Device|null $device
 * @property-read Collection<int, \App\Models\Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, \App\Models\OrderLine> $orderLines
 * @property-read int|null $order_lines_count
 * @property-read Model|\Eloquent $orderable
 * @property-read Collection<int, \App\Models\Price> $prices
 * @property-read int|null $prices_count
 * @property-read \App\Models\User $user
 * @method static Builder|Order newModelQuery()
 * @method static Builder|Order newQuery()
 * @method static Builder|Order query()
 * @method static Builder|Order whereCreatedAt($value)
 * @method static Builder|Order whereDeliveryAddress($value)
 * @method static Builder|Order whereDeviceId($value)
 * @method static Builder|Order whereId($value)
 * @method static Builder|Order whereNote($value)
 * @method static Builder|Order whereOrderableId($value)
 * @method static Builder|Order whereOrderableType($value)
 * @method static Builder|Order wherePaid($value)
 * @method static Builder|Order whereScheduledAt($value)
 * @method static Builder|Order whereStatus($value)
 * @method static Builder|Order whereSubtotalPrice($value)
 * @method static Builder|Order whereTotalPrice($value)
 * @method static Builder|Order whereUpdatedAt($value)
 * @method static Builder|Order whereUserId($value)
 * @mixin Eloquent
 */
	class Order extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\OrderLine
 *
 * @property int $id
 * @property string|null $note
 * @property int|null $item_id
 * @property int|null $order_id
 * @property int|null $content_id
 * @property int|null $user_id
 * @property int|null $count
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property float $total_price
 * @property float $subtotal_price
 * @property array|null $data
 * @property-read Collection<int, \App\Models\Addon> $addons
 * @property-read int|null $addons_count
 * @property-read Collection<int, \App\Models\Discount> $discounts
 * @property-read int|null $discounts_count
 * @property-read \App\Models\Item|null $item
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, \App\Models\Price> $prices
 * @property-read int|null $prices_count
 * @property-read \App\Models\Reservation|null $reservation
 * @property-read \App\Models\User|null $user
 * @method static Builder|OrderLine newModelQuery()
 * @method static Builder|OrderLine newQuery()
 * @method static Builder|OrderLine query()
 * @method static Builder|OrderLine whereContentId($value)
 * @method static Builder|OrderLine whereCount($value)
 * @method static Builder|OrderLine whereCreatedAt($value)
 * @method static Builder|OrderLine whereData($value)
 * @method static Builder|OrderLine whereId($value)
 * @method static Builder|OrderLine whereItemId($value)
 * @method static Builder|OrderLine whereNote($value)
 * @method static Builder|OrderLine whereOrderId($value)
 * @method static Builder|OrderLine whereSubtotalPrice($value)
 * @method static Builder|OrderLine whereTotalPrice($value)
 * @method static Builder|OrderLine whereUpdatedAt($value)
 * @method static Builder|OrderLine whereUserId($value)
 * @mixin Eloquent
 */
	class OrderLine extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Package
 *
 * @property int $id
 * @property int $days
 * @property string $type
 * @property string $slug
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, \App\Models\Order> $orders
 * @property-read int|null $orders_count
 * @property-read Collection<int, \App\Models\Price> $prices
 * @property-read int|null $prices_count
 * @method static Builder|Package newModelQuery()
 * @method static Builder|Package newQuery()
 * @method static Builder|Package query()
 * @method static Builder|Package whereCreatedAt($value)
 * @method static Builder|Package whereDays($value)
 * @method static Builder|Package whereId($value)
 * @method static Builder|Package whereSlug($value)
 * @method static Builder|Package whereType($value)
 * @method static Builder|Package whereUpdatedAt($value)
 * @mixin Eloquent
 */
	class Package extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Price
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property float $price
 * @property string $priceable_type
 * @property int $priceable_id
 * @property int $user_id
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Model|\Eloquent $priceable
 * @property-read \App\Models\User $user
 * @method static Builder|Price newModelQuery()
 * @method static Builder|Price newQuery()
 * @method static Builder|Price query()
 * @method static Builder|Price whereCreatedAt($value)
 * @method static Builder|Price whereId($value)
 * @method static Builder|Price wherePrice($value)
 * @method static Builder|Price wherePriceableId($value)
 * @method static Builder|Price wherePriceableType($value)
 * @method static Builder|Price whereUpdatedAt($value)
 * @method static Builder|Price whereUserId($value)
 * @mixin Eloquent
 */
	class Price extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Reservation
 *
 * @property int $id
 * @property string $from
 * @property string $to
 * @property int|null $reservable_id
 * @property string|null $reservable_type
 * @property array|null $data
 * @property string $status
 * @property int|null $order_id
 * @property int|null $order_line_id
 * @property int|null $reserved_by_id
 * @property int|null $reserved_for_id
 * @property int|null $business_id
 * @property int|null $branch_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Branch|null $branch
 * @property-read \App\Models\Business|null $business
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property-read int|null $invoices_count
 * @property-read \App\Models\Order|null $order
 * @property-read \App\Models\OrderLine|null $orderline
 * @property-read Model|\Eloquent $reservable
 * @property-read \App\Models\User|null $reservedBy
 * @property-read \App\Models\User|null $reservedFor
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereBranchId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereBusinessId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereFrom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereOrderLineId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereReservableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereReservableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereReservedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereReservedForId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereTo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property array|null $notes
 * @property int|null $follower_id
 * @property int $unit
 * @property int|null $seat_id
 * @property-read \App\Models\User|null $follower
 * @property-read mixed $payment_status
 * @property-read \App\Models\Seat|null $seat
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereFollowerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereSeatId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Reservation whereUnit($value)
 */
	class Reservation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Seat
 *
 * @property int $id
 * @property int|null $area_id
 * @property int $sort
 * @property-read Collection<int, Locales> $locales
 * @property-read int|null $locales_count
 * @method static Builder|Seat newModelQuery()
 * @method static Builder|Seat newQuery()
 * @method static Builder|Seat query()
 * @method static Builder|Seat whereAreaId($value)
 * @method static Builder|Seat whereId($value)
 * @method static Builder|Seat whereSort($value)
 * @mixin Eloquent
 */
	class Seat extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Service
 *
 * @property int $id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property string $serviceable_type
 * @property int $serviceable_id
 * @property int $user_id
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read \App\Models\User $user
 * @method static Builder|Service newModelQuery()
 * @method static Builder|Service newQuery()
 * @method static Builder|Service query()
 * @method static Builder|Service whereCreatedAt($value)
 * @method static Builder|Service whereId($value)
 * @method static Builder|Service whereServiceableId($value)
 * @method static Builder|Service whereServiceableType($value)
 * @method static Builder|Service whereUpdatedAt($value)
 * @method static Builder|Service whereUserId($value)
 * @mixin Eloquent
 * @property-read \App\Models\Media|null $featuredImage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Price> $prices
 * @property-read int|null $prices_count
 */
	class Service extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Setting
 *
 * @property int $id
 * @property string $key
 * @property array|null $data
 * @property string $settable_type
 * @property int $settable_id
 * @property int $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static Builder|Setting newModelQuery()
 * @method static Builder|Setting newQuery()
 * @method static Builder|Setting query()
 * @method static Builder|Setting whereCreatedAt($value)
 * @method static Builder|Setting whereData($value)
 * @method static Builder|Setting whereId($value)
 * @method static Builder|Setting whereKey($value)
 * @method static Builder|Setting whereSettableId($value)
 * @method static Builder|Setting whereSettableType($value)
 * @method static Builder|Setting whereUpdatedAt($value)
 * @method static Builder|Setting whereUserId($value)
 * @mixin Eloquent
 */
	class Setting extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Subscription
 *
 * @property int $id
 * @property int|null $package_id
 * @property int|null $creator_id
 * @property int|null $user_id
 * @property string $status
 * @property string|null $from
 * @property string|null $to
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Collection<int, \App\Models\Locales> $locales
 * @property-read int|null $locales_count
 * @property-read \App\Models\Package|null $package
 * @property-read \App\Models\User|null $user
 * @method static Builder|Subscription newModelQuery()
 * @method static Builder|Subscription newQuery()
 * @method static Builder|Subscription query()
 * @method static Builder|Subscription whereCreatedAt($value)
 * @method static Builder|Subscription whereCreatorId($value)
 * @method static Builder|Subscription whereFrom($value)
 * @method static Builder|Subscription whereId($value)
 * @method static Builder|Subscription wherePackageId($value)
 * @method static Builder|Subscription whereStatus($value)
 * @method static Builder|Subscription whereTo($value)
 * @method static Builder|Subscription whereUpdatedAt($value)
 * @method static Builder|Subscription whereUserId($value)
 * @mixin Eloquent
 */
	class Subscription extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $two_factor_secret
 * @property string|null $two_factor_recovery_codes
 * @property string|null $two_factor_confirmed_at
 * @property string|null $remember_token
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property string|null $provider
 * @property string|null $provider_id
 * @property int|null $business_id
 * @property-read Collection<int, \App\Models\Business> $business
 * @property-read int|null $business_count
 * @property-read Collection<int, \App\Models\Category> $categories
 * @property-read int|null $categories_count
 * @property-read Collection<int, \App\Models\Contact> $contacts
 * @property-read int|null $contacts_count
 * @property-read Collection<int, \App\Models\Device> $devices
 * @property-read int|null $devices_count
 * @property-read Collection<int, \App\Models\Item> $items
 * @property-read int|null $items_count
 * @property-read Collection<int, \App\Models\Media> $media
 * @property-read int|null $media_count
 * @property-read DatabaseNotificationCollection<int, DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read Collection<int, Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read string $profile_photo_url
 * @property-read Collection<int, Role> $roles
 * @property-read int|null $roles_count
 * @property-read Collection<int, \App\Models\Service> $services
 * @property-read int|null $services_count
 * @property-read Collection<int, \App\Models\Setting> $settings
 * @property-read int|null $settings_count
 * @property-read Collection<int, \App\Models\Subscription> $subscriptions
 * @property-read int|null $subscriptions_count
 * @property-read Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static Builder|User newModelQuery()
 * @method static Builder|User newQuery()
 * @method static Builder|User permission($permissions, $without = false)
 * @method static Builder|User query()
 * @method static Builder|User role($roles, $guard = null, $without = false)
 * @method static Builder|User whereBusinessId($value)
 * @method static Builder|User whereCreatedAt($value)
 * @method static Builder|User whereEmail($value)
 * @method static Builder|User whereEmailVerifiedAt($value)
 * @method static Builder|User whereId($value)
 * @method static Builder|User whereName($value)
 * @method static Builder|User wherePassword($value)
 * @method static Builder|User wherePhone($value)
 * @method static Builder|User whereProvider($value)
 * @method static Builder|User whereProviderId($value)
 * @method static Builder|User whereRememberToken($value)
 * @method static Builder|User whereTwoFactorConfirmedAt($value)
 * @method static Builder|User whereTwoFactorRecoveryCodes($value)
 * @method static Builder|User whereTwoFactorSecret($value)
 * @method static Builder|User whereUpdatedAt($value)
 * @method static Builder|User withoutPermission($permissions)
 * @method static Builder|User withoutRole($roles, $guard = null)
 * @mixin Eloquent
 * @property array|null $control
 * @property bool $dashboard_access
 * @property string|null $profile_photo_path
 * @property bool|null $is_employee
 * @property-read \App\Models\Media|null $featuredImage
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Reservation> $followingReservations
 * @property-read int|null $following_reservations_count
 * @property-read mixed $business_control
 * @method static \Illuminate\Database\Eloquent\Builder|User whereControl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDashboardAccess($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereIsEmployee($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProfilePhotoPath($value)
 */
	class User extends \Eloquent {}
}

