<?php


namespace App\Actions;


use App\Models\Price;
use App\Repository\Eloquent\PriceRepository;

class PriceActions
{
    private $repository;

    public function __construct(PriceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function processPrice(array $data)
    {
        return array_only($data, ['price', 'user_id']);
    }

    public function createPrice(array $data, $pricable)
    {
        $price = $this->repository->create(
            $this->processPrice($data) + [
                'priceable_type'=>$pricable::class,
                'priceable_id'=>$pricable->id
            ]);
        $this->createPriceLocale($price, $data);
        return $price;
    }

    public function createPriceLocale($price, $data)
    {
        foreach ($data['price_locales'] as &$locale)
            app(LocaleAction::class)->createLocale([
                "name" => $locale['price_name'],
                "locale" => $locale['price_locale'] ?? \App::getLocale(),
                "localizable_type" => Price::class,
                "localizable_id" => $price->id
            ]);
    }



}
