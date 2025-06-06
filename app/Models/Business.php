<?php


namespace App\Models;


use App\Traits\Configurable;
use App\Traits\Contactable;
use App\Traits\Contentable;
use App\Traits\Discountable;
use App\Traits\Localizable;
use App\Traits\Mediable;
use App\Traits\Orderable;
use App\Traits\Settable;
use Eloquent;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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
 */
class Business extends Model
{
    use Localizable, Settable, Contactable, Orderable, Mediable, Discountable, Contentable, Configurable;

    protected $guarded = ['id'];
    protected $table = "business";

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class)->where('parent_id', null)->orderBy('sort');
    }

    public function branches(): HasMany
    {
        return $this->hasMany(Branch::class);
    }

    public function menus(): HasMany{
        return $this->hasMany(Menu::class);
    }
}
