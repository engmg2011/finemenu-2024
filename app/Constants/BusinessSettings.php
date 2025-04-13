<?php

namespace App\Constants;

class BusinessSettings
{
    const Locales = "Locales";
    const Logo = "Logo";
    const PrimaryColor = "PrimaryColor";
    const AccentColor = "AccentColor";
    const TextColor = "TextColor";

    const TimeZone = "TimeZone";
    const Address = "Address";

    const GeneralDiscount = "GeneralDiscount";
    const ExtraCharge = "ExtraCharge";
    const PreviewPrices = "PreviewPrices";

    const Website = "Website";
    const Facebook = "Facebook";
    const Instagram = "Instagram";
    const X = "X";
    const ContactPhone = "ContactPhone";
    const Whatsapp = "Whatsapp";
    const Linkedin = "Linkedin";
    const Youtube = "Youtube";
    const Snapchat = "Snapchat";

    const DineIn = "DineIn";
    const Delivery = "Delivery";
    const SelfPickUp = "SelfPickUp";
    const CarPickup = "CarPickup";
    const Currency = "Currency";
    const EnableReservationsTill = "EnableReservationTill";

    const PayCash = "PayCash";
    const PayOnline = "PayOnline";

    public static function getConstants(): array
    {
        $reflectionClass = new \ReflectionClass(self::class);
        return $reflectionClass->getConstants();
    }
}
