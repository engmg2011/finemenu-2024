<?php

namespace App\Traits;

use App\Models\Feature;

trait Featurable
{

    public static function bootFeaturable()
    {
        static::retrieved(function ($model) {
            // Hide features from the model
            $model->makeHidden(array_merge($model->getHidden(), ['features']));

            // Add custom appended attributes
            $model->append(array_merge($model->appends, ['featuresData']));
        });

    }

    public function features()
    {
        return $this->morphToMany(Feature::class , 'featureable')
            ->using(\App\Models\Featurable::class) // custom pivot model
            ->withPivot('value', 'value_unit','sort','category_id');
    }

    public function getFeaturesDataAttribute()
    {
        return $this->features->map(function ($feature) {
            return [
                'id'         => $feature->id,
                'key'        => $feature->key,
                'type'       => $feature->type,
                'value'      => $feature->pivot->value ?? null,
                'value_unit' => $feature->pivot->value_unit ?? null,
                'sort'       => $feature->pivot->sort ?? null,
                'category_id'       => $feature->pivot->category_id ?? null
            ];
        });
    }
}
