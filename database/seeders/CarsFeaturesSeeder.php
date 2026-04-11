<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Feature;
use App\Models\Items\Cars\CarProduct;
use App\Repository\Eloquent\CategoryRepository;
use App\Repository\Eloquent\FeatureOptionsRepository;
use App\Repository\Eloquent\FeatureRepository;
use Illuminate\Database\Seeder;

class CarsFeaturesSeeder extends Seeder
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
          "Engine & Performance": {
            "count": 5,
            "fields": {
              "engine_type": ["Petrol", "Diesel", "Hybrid", "Electric"],
              "engine_capacity_cc": "number",
              "horsepower_hp": "number",
              "transmission": ["Manual", "Automatic", "CVT", "Dual Clutch"],
              "drive_type": ["FWD", "RWD", "AWD", "4WD"]
            }
          },
          "Fuel & Efficiency": {
            "count": 3,
            "fields": {
              "fuel_tank_capacity_liters": "number",
              "fuel_consumption": "number",
              "eco_mode": "boolean"
            }
          },
          "Exterior Features": {
            "count": 6,
            "fields": {
              "body_type": ["Sedan", "SUV", "Hatchback", "Coupe", "Convertible", "Pickup"],
              "led_headlights": "boolean",
              "daytime_running_lights": "boolean",
              "alloy_wheels": ["Steel", "Alloy", "Sport Alloy"],
              "sunroof": ["None", "Manual", "Panoramic"],
              "power_mirrors": "boolean"
            }
          },
          "Interior & Comfort": {
            "count": 6,
            "fields": {
              "seat_material": ["Fabric", "Leather", "Leatherette"],
              "seat_adjustment": ["Manual", "Electric"],
              "climate_control": ["Manual AC", "Automatic AC", "Dual Zone"],
              "heated_seats": "boolean",
              "steering_wheel_controls": "boolean",
              "ambient_lighting": "boolean"
            }
          },
          "Safety & Security": {
            "count": 6,
            "fields": {
              "airbags": "number",
              "abs": "boolean",
              "traction_control": "boolean",
              "lane_assist": "boolean",
              "blind_spot_monitor": "boolean",
              "rear_view_camera": "boolean"
            }
          },
          "Technology & Infotainment": {
            "count": 5,
            "fields": {
              "infotainment_screen_size_inches": "number",
              "apple_carplay": "boolean",
              "android_auto": "boolean",
              "bluetooth": "boolean",
              "wireless_charging": "boolean"
            }
          },
          "Convenience & Utility": {
            "count": 5,
            "fields": {
              "keyless_entry": "boolean",
              "push_start_button": "boolean",
              "cruise_control": ["Standard", "Adaptive"],
              "power_tailgate": "boolean",
              "parking_sensors": ["Rear", "Front & Rear"]

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
            "itemable_type": "Car",
            "locales": [
            {
              "name": "' . $name . '",
              "locale": "en"
            }
            ]}', true);

        $category = $this->categoryRepository->createModel($cat);
        Category::find($category->id)->update(['itemable_type' => CarProduct::class]);
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
            "itemable_type": "Car",
            "category_id" : "' . $category_id . '"
        }', true));

        Feature::find($feature->id)->update(['itemable_type' => CarProduct::class]);
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


    public function updateIcons()
    {
         Feature::whereHas('locales', function($q){ $q->where('name','like','%engine_type%'); })->first()?->update(['icon'=>'engine']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%engine_capacity_cc%'); })->first()?->update(['icon'=>'engine-outline']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%horsepower_hp%'); })->first()?->update(['icon'=>'speedometer']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%transmission%'); })->first()?->update(['icon'=>'car-shift-pattern']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%drive_type%'); })->first()?->update(['icon'=>'car-4x4']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%fuel_tank_capacity_liters%'); })->first()?->update(['icon'=>'fuel']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%fuel_consumption%'); })->first()?->update(['icon'=>'leaf']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%eco_mode%'); })->first()?->update(['icon'=>'leaf-circle']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%body_type%'); })->first()?->update(['icon'=>'car-side']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%led_headlights%'); })->first()?->update(['icon'=>'car-light-high']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%daytime_running_lights%'); })->first()?->update(['icon'=>'car-light-dimmed']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%alloy_wheels%'); })->first()?->update(['icon'=>'tire']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%sunroof%'); })->first()?->update(['icon'=>'car-convertible']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%power_mirrors%'); })->first()?->update(['icon'=>'car-door']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%seat_material%'); })->first()?->update(['icon'=>'car-seat']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%seat_adjustment%'); })->first()?->update(['icon'=>'seat-recline-normal']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%climate_control%'); })->first()?->update(['icon'=>'air-conditioner']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%heated_seats%'); })->first()?->update(['icon'=>'seat']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%steering_wheel_controls%'); })->first()?->update(['icon'=>'steering']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%ambient_lighting%'); })->first()?->update(['icon'=>'lightbulb-on-outline']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%airbags%'); })->first()?->update(['icon'=>'airbag']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%abs%'); })->first()?->update(['icon'=>'car-brake-abs']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%traction_control%'); })->first()?->update(['icon'=>'car-traction-control']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%lane_assist%'); })->first()?->update(['icon'=>'car-lane']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%blind_spot_monitor%'); })->first()?->update(['icon'=>'car-arrow-right']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%rear_view_camera%'); })->first()?->update(['icon'=>'camera-rear']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%infotainment_screen_size_inches%'); })->first()?->update(['icon'=>'monitor-dashboard']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%apple_carplay%'); })->first()?->update(['icon'=>'apple']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%android_auto%'); })->first()?->update(['icon'=>'android']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%bluetooth%'); })->first()?->update(['icon'=>'bluetooth']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%wireless_charging%'); })->first()?->update(['icon'=>'cellphone-wireless']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%keyless_entry%'); })->first()?->update(['icon'=>'keyless']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%push_start_button%'); })->first()?->update(['icon'=>'engine-start']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%cruise_control%'); })->first()?->update(['icon'=>'speedometer-medium']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%power_tailgate%'); })->first()?->update(['icon'=>'car-back']);
         Feature::whereHas('locales', function($q){ $q->where('name','like','%parking_sensors%'); })->first()?->update(['icon'=>'parking']);


    }
}
