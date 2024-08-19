<?php

namespace App\Constants {
    class BusinessTypes
    {
        public final const RESTAURANT = "restaurant";
        public final const SALON = "salon";
        public final const HOTEL = "hotel";

        public static function all(): array
        {
            return [
                BusinessTypes::RESTAURANT,
                BusinessTypes::SALON,
                BusinessTypes::HOTEL,
            ];
        }
    }

}
