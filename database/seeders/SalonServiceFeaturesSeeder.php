<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Feature;
use App\Models\Items\Chalet;
use App\Models\Items\SalonService;
use App\Repository\Eloquent\CategoryRepository;
use App\Repository\Eloquent\FeatureOptionsRepository;
use App\Repository\Eloquent\FeatureRepository;
use Illuminate\Database\Seeder;

class SalonServiceFeaturesSeeder extends Seeder
{

    public function __construct(private CategoryRepository       $categoryRepository,
                                private FeatureRepository        $featureRepository,
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
              "Hair Services": {
                "count": 5,
                "fields": {
                  "haircut": ["Men", "Women", "Children"],
                  "hair_coloring": ["Full Color", "Highlights", "Balayage", "Root Touch-up", "Ombre"],
                  "hair_styling": ["Blow Dry", "Updo", "Braiding", "Straightening", "Curling"],
                  "hair_treatments": ["Keratin", "Deep Conditioning", "Scalp Treatment", "Hair Spa"],
                  "hair_extensions": ["Clip-in", "Tape-in", "Fusion", "Micro-link"]
                }
              },
              "Beauty & Skincare": {
                "count": 5,
                "fields": {
                  "facials": ["Hydrating", "Anti-Aging", "Acne Treatment", "Brightening", "Sensitive Skin"],
                  "manicure_pedicure": ["Basic", "Gel", "Acrylic", "Spa Treatment"],
                  "waxing": ["Eyebrows", "Legs", "Arms", "Full Body"],
                  "eyebrow_shaping": ["Threading", "Waxing", "Tweezing", "Microblading"],
                  "makeup_services": ["Day Makeup", "Evening Makeup", "Bridal", "Photoshoot"]
                }
              },
              "Massage & Relaxation": {
                "count": 4,
                "fields": {
                  "swedish_massage": ["30 min", "60 min", "90 min"],
                  "deep_tissue_massage": ["30 min", "60 min", "90 min"],
                  "aromatherapy": ["Lavender", "Rose", "Eucalyptus", "Custom Blend"],
                  "hot_stone_massage": ["30 min", "60 min"]
                }
              },
              "Wellness & Therapy": {
                "count": 3,
                "fields": {
                  "sauna": ["Dry Sauna", "Infrared Sauna", "Steam Sauna"],
                  "steam_room": ["Traditional", "Herbal Steam"],
                  "hydrotherapy": ["Jacuzzi", "Vichy Shower", "Foot Bath"]
                }
              },
              "Facilities & Amenities": {
                "count": 5,
                "fields": {
                  "free_wifi": "boolean",
                  "parking": ["Covered", "Open", "Valet"],
                  "refreshments": ["Tea", "Coffee", "Juices", "Snacks"],
                  "air_conditioning": "boolean",
                  "tv_magazine_area": "boolean"
                }
              },
              "Kids & Family Services": {
                "count": 3,
                "fields": {
                  "kids_haircut": ["Boys", "Girls"],
                  "kids_play_area": "boolean",
                  "family_rooms": "boolean"
                }
              }
            }
', true);

    }


    public function createCategory($name)
    {
        $cat = json_decode('{
            "user_id": 1,
            "menu_id": null,
            "type": "features",
            "icon": "",
            "icon-font-type": "mdi",
            "itemable_type": "SalonService",
            "locales": [
            {
              "name": "' . $name . '",
              "locale": "en"
            }
            ]}', true);

        $category = $this->categoryRepository->createModel($cat);
        Category::find($category->id)->update(['itemable_type' => SalonService::class]);
        return $category;
    }

    public function createFeature($name, $type, $category_id)
    {
        $slug = slug($name);
        $feature = $this->featureRepository->createModel(json_decode('{
            "key": "' . $slug . '",
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

        Feature::find($feature->id)->update(['itemable_type' => SalonService::class]);
        return $feature;
    }

    public function createFeatureOption($options, $featureId)
    {
        $index = 1;
        foreach ($options as $option) {
            $fOption = json_decode('{
                "feature_id": "' . $featureId . '",
                "locales": [
                    {
                        "name": "' . $option . '",
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
                if (is_array($featureType)) {
                    $feature = $this->createFeature($featureName, "array", $category->id);
                    $this->createFeatureOption($featureType, $feature->id);
                    print_r($featureType);
                } else {
                    echo "fname $featureName  ,featureType $featureType" . PHP_EOL;
                    $this->createFeature($featureName, $featureType, $category->id);
                }
            }

        }
    }
}
