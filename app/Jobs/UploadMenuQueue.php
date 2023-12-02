<?php

namespace App\Jobs;

use App\Actions\CategoryAction;
use App\Actions\ItemAction;
use App\Actions\MediaAction;
use App\Models\Category;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UploadMenuQueue implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

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
        if (count($splitNames)) {
            $categories = (app(CategoryAction::class))
                ->createCategoriesFromPath($splitNames, $this->myFile['uploadedFilePath'], $this->user['userId'], $this->user['restaurantId']);
            $current_categories = $categories->all();
            $savingCategory = end($current_categories);
        } else {
            // TODO :: first user locale
            $savingCategory = app(CategoryAction::class)->create([
                "locales" => [["name" => 'Others', 'locale' => $this->user['locale']]],
                "image" => $this->myFile['uploadedFilePath'],
                "user_id" => $this->user['userId'],
                "restaurant_id" => $this->user['restaurantId']]);
        }
        // TODO :: Pass first user locale
        $item = app(ItemAction::class)->create([
            'locales' => [['name' => $item_name, 'locale' => $this->user['locale']]],
            'category_id' => ($savingCategory->id),
            'user_id' => $this->user['userId']
        ]);
        $categoryImages = Category::with('media')->find($savingCategory->id)->media;
        if (count($categoryImages) === 0) {
            $mediaAction->storeMedia($this->myFile['uploadedFilePath'], $this->myFile['fileType'], $item_name, $savingCategory);
        }
        $mediaAction->storeMedia($this->myFile['uploadedFilePath'], $this->myFile['fileType'], $item_name, $item);
    }
}
