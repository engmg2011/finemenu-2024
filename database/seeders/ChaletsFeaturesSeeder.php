<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Feature;
use App\Models\Items\Chalet;
use App\Repository\Eloquent\CategoryRepository;
use App\Repository\Eloquent\FeatureOptionsRepository;
use App\Repository\Eloquent\FeatureRepository;
use App\Repository\Eloquent\MenuRepository;
use Illuminate\Database\Seeder;

class ChaletsFeaturesSeeder extends Seeder
{

    public function __construct(private CategoryRepository $categoryRepository,
                                private FeatureRepository  $featureRepository,
    private FeatureOptionsRepository $featureOptionsRepository,)
    {

    }

    /**
     *
     * @return mixed
     */

    public function featuresData()
    {
        return json_decode('{
          "Bedroom Details": {
            "count": 2,
            "fields": {
              "total_bedrooms": "number",
              "master_bedrooms": "number"
            }
          },
          "Swimming Pool": {
            "count": 3,
            "fields": {
              "pool_type": ["No Pool", "Outdoor Pool", "Indoor Pool"],
              "pool_has_shutters": "boolean",
              "pool_has_heating": "boolean"
            }
          },
          "Kitchen Facilities": {
            "count": 3,
            "fields": {
              "fully_equipped_kitchen": "boolean",
              "basic_kitchen": "boolean",
              "preparatory_kitchen": "boolean"
            }
          },
          "Rooms & Spaces": {
            "count": 4,
            "fields": {
              "living_rooms": "number",
              "gathering_rooms_majlis": "number",
              "maids_room": "boolean",
              "outdoor_seating_area": "boolean"
            }
          },
          "Entertainment & Recreation": {
            "count": 8,
            "fields": {
              "bbq_equipment": "boolean",
              "kids_playground": "boolean",
              "kayak_available": "boolean",
              "baby_foot_foosball_table": "boolean",
              "tennis_table_ping_pong": "boolean",
              "hockey_table_air_hockey": "boolean",
              "cinema_home_theater": "boolean",
              "tv": "boolean",
              "garden": "boolean"
            }
          },
          "Building Features & Facilities": {
            "count": 4,
            "fields": {
              "elevator": "boolean",
              "parking": "boolean",
              "free_wifi": "boolean"
            }
          }
        }', true);

    }


    public function createCategory($name)
    {
        $cat = json_decode('{
            "user_id": 1,
            "menu_id": null,
            "type": "features",
            "icon": "fa fa-icon",
            "icon-font-type": "mdi",
            "itemable_type": "Chalet",
            "locales": [
            {
              "name": "' . $name . '",
              "locale": "en"
            }
            ]}', true);

        $category = $this->categoryRepository->createModel($cat);
        Category::find($category->id)->update(['itemable_type' => Chalet::class]);
        return $category;
    }

    public function createFeature($name, $type, $category_id)
    {
        $slug = slug($name);
        $feature = $this->featureRepository->createModel(json_decode('{
            "key": "'.$slug.'",
            "type": "' . $type . '",
            "icon": "",
            "icon-font-type": "mdi",
            "color": "",
            "locales": [
                {
                    "name": "' . $name . '",
                    "description": null,
                    "locale": "en"
                }
            ],
            "itemable_type": "Chalet",
            "category_id" : "' . $category_id . '"
        }', true));

        Feature::find($feature->id)->update(['itemable_type' => Chalet::class]);
        return $feature;
    }

    public function createFeatureOption($options, $featureId)
    {
        $index = 1;
        foreach ($options as $option) {
            $fOption = json_decode('{
                "feature_id": "' . $featureId. '",
                "locales": [
                    {
                        "name": "'.$option.'",
                        "description": null,
                        "locale": "en"
                    }
                ],
                "sort": ' . $index . '
            }', true);
            $this->featureOptionsRepository->createModel($fOption);
            $index++;
        }
    }

    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach ($this->featuresData() as $categoryName => $features) {
            $category = $this->createCategory($categoryName);
            echo "cat $categoryName " . PHP_EOL;
            foreach ($features['fields'] as $featureName => $featureType) {
                if(is_array($featureType)) {
                    $feature = $this->createFeature($featureName, "array" , $category->id);
                    $this->createFeatureOption($featureType, $feature->id);
                    print_r($featureType);
                }
                else{
                    echo "fname $featureName  ,featureType $featureType" . PHP_EOL;
                    $this->createFeature($featureName, $featureType , $category->id);
                }
            }

        }
    }
}
