<?php

namespace App\Jobs;

use App\Actions\CategoryAction;
use App\Actions\ItemAction;
use App\Actions\MediaAction;
use App\Models\Category;
use App\Models\Menu;
use App\Repository\Eloquent\ItemRepository;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UploadMenuQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public const OthersName = 'Others';

    /**
     * UploadMenuQueue constructor.
     * @param $myFile
     * @param $user
     */
    public function __construct(private $myFile, private $user)
    {

    }

    /**
     * To make a fine name from file name
     * @param $name
     * @return string
     */
    private function fineName($name)
    {
        $name = str_ireplace('-', ' ', $name);
        $name = str_ireplace('_', ' ', $name);
        $name = str_ireplace('+', ' ', $name);
        return ucfirst($name);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $mediaAction = app(MediaAction::class);
        $splitNames = explode('/', $this->myFile['fullPath']);
        $item_name = array_pop($splitNames);
        $item_name = $this->fineName(explode('.', $item_name)[0]);
        $menu = Menu::find($this->user['menuId']);
        if (count($splitNames)) {
            $categories = (app(CategoryAction::class))
                ->createCategoriesFromPath(
                    $splitNames,
                    $this->myFile['uploadedFilePath'],
                    $this->user['userId'],
                    $this->user['menuId']
                );
            $current_categories = $categories->all();
            $savingCategory = end($current_categories);
        } else {
            // TODO :: first user locale

            $savingCategory = Category::where([
                "menu_id" => $this->user['menuId']
            ])->whereHas('locales', function ($q){
                $q->where('name',self::OthersName);
            })->first();
            if(!$savingCategory){
                $savingCategory = app(CategoryAction::class)->create([
                    "locales" => [["name" => self::OthersName, 'locale' => $this->user['locale']]],
                    "image" => $this->myFile['uploadedFilePath'],
                    "user_id" => $this->user['userId'],
                    "business_id" => $menu->business_id,
                    "menu_id" => $this->user['menuId']
                ]);
            }

        }
        // TODO :: Pass first user locale
        $item = app(ItemRepository::class)->create([
            'locales' => [['name' => $item_name, 'locale' => $this->user['locale']]],
            'category_id' => ($savingCategory->id),
            'user_id' => $this->user['userId'],
            "business_id" => $menu->business_id
        ]);
        $categoryImages = Category::with('media')->find($savingCategory->id)->media;
        if (count($categoryImages) === 0) {
            $mediaAction->storeMedia($this->myFile['uploadedFilePath'], $this->myFile['fileType'], $item_name, $savingCategory);
        }
        $mediaAction->storeMedia($this->myFile['uploadedFilePath'], $this->myFile['fileType'], $item_name, $item);
    }
}
