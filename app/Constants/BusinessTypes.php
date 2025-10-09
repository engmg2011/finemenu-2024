<?php

namespace App\Constants {
    class BusinessTypes
    {
        public final const RESTAURANT = "restaurant";
        public final const SALON = "salon";
        public final const HOTEL = "hotel";
        public final const CHALET = "chalet";
        public final const CARS = "cars";

        public static function all(): array
        {
            return [
                BusinessTypes::RESTAURANT,
                BusinessTypes::SALON,
                BusinessTypes::HOTEL,
                BusinessTypes::CHALET,
                BusinessTypes::CARS
            ];
        }
    }

}
