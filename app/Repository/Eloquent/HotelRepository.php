<?php

namespace App\Repository\Eloquent;



use App\Models\Hotel;
use App\Repository\HotelRepositoryInteface;

class HotelRepository extends BaseRepository implements HotelRepositoryInteface
{
    /**
     * UserRepository constructor.
     * @param Hotel $model
     */
    public function __construct(Hotel $model) {
        parent::__construct($model);
    }

}
