<?php

namespace Database\Seeders;

use App\Actions\CategoryAction;
use App\Constants\PaymentConstants;
use App\Models\Item;
use App\Models\Items\Chalet;
use App\Models\Locales;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Database\Seeder;

class ShalehiSeeder extends Seeder
{
    const BUSINESS_ID = 1;
    const BRANCH_ID = 1;
    const MENU_ID = 1;
    const USER_ID = 1;

    public function __construct(private CategoryAction $categoryAction)
    {
    }

    public function category()
    {
        $category = $this->categoryAction->create(json_decode('{
              "user_id": ' . self::USER_ID . ',
              "menu_id": ' . self::MENU_ID . ',
              "locales": [
                {
                  "name": "Shalehi Backup",
                  "locale": "en"
                }
              ]}', true));
        return $category;
    }

    public function employee($data)
    {
        $user = [
            'name' => $data['employee'] ?? "employee",
            'email' => "employee-" . strtoupper(uniqid()) . "@shalehi.net",
            'password' => bcrypt(uniqid() . uniqid()),
        ];
        return User::firstOrCreate(['name' => $data['employee'] ?? "employee"], $user);
    }

    public function customer($data)
    {
        $user = [
            'name' => $data['client'],
            'email' => "client-" . strtoupper(uniqid()) . "@shalehi.net",
            'phone' => @$data['phone'],
            'password' => bcrypt(uniqid() . uniqid()),
        ];
        return User::firstOrCreate(['name' => $data['client']], $user);
    }

    public function chalet($data, $category)
    {
        $locale = Locales::where(["name" => $data['chalet']])->first();
        if ($locale && $locale->localizable_id !== null && $locale->localizable_type == "App\Models\Item") {
            $item = Item::find($locale->localizable_id);
        } else {
            $item = Item::create(['user_id' => 1, 'category_id' => $category->id]);
            $chalet = Chalet::create(['item_id' => $item->id , 'insurance' => $data['chalet']['insurance']]);
            $item->itemable()->associate($chalet);
            $item->save();

            Locales::create([
                "locale" => "en",
                "name" => $data['chalet'],
                'localizable_type' => "App\Models\Item",
                'localizable_id' => $item->id])->first();
        }
        return $item;
    }

    public function reservation($data, $item, $employee, $customer)
    {
        $reservation = [
            "from" => $data['from'] . " 14:00",
            "to" => $data['to'] . " 14:00",
            "reservable_id" => $item->id,
            "reservable_type" => "App\Models\Item",
            "status" => PaymentConstants::RESERVATION_COMPLETED,
            "reserved_by_id" => $employee->id,
            "reserved_for_id" => $customer->id,
            "business_id" => self::BUSINESS_ID,
            "branch_id" => self::BRANCH_ID,
        ];
        return Reservation::create($reservation);
        // needs many to many relation
//        $reservation2 = [
//            "from" => $data['from2'] . " 14:00",
//            "to" => $data['to2'] . " 14:00",
//        ];
//        $reservation2 = [
//            "from" => $data['from3'] . " 14:00",
//            "to" => $data['to3'] . " 14:00",
//        ];
    }

    public function invoices($data, $reservation, $employee, $customer)
    {
        $commonData = [
            "reservation_id" => $reservation->id,
            "invoice_by_id" => $employee->id,
            "invoice_for_id" => $customer->id,
            "business_id" => self::BUSINESS_ID,
            "branch_id" => self::BRANCH_ID,];

        $paidInvoices = [[
            "type" => "credit",
            "amount" => $data['paid'] ?? 0,
            "payment_type" => $data['paymentType'] ?? PaymentConstants::TYPE_KNET,
            "status" => PaymentConstants::INVOICE_PAID,
            ...$commonData
        ], [
            "type" => "credit",
            "amount" => $data['paid2'] ?? 0,
            "payment_type" => $data['paymentType'] ?? PaymentConstants::TYPE_KNET,
            "status" => PaymentConstants::INVOICE_PAID,
            ...$commonData
        ], [
            "type" => "debit",
            "amount" => $data['insurance'] ?? 0,
            "payment_type" => $data['insurancePaymentType'] ?? PaymentConstants::TYPE_KNET,
            "status" => PaymentConstants::INVOICE_REFUNDED,
            ...$commonData
        ]];
        \DB::table('invoices')->insert($paidInvoices);
    }


    public function run(): void
    {
        $category = $this->category();
        // import data
        $jsonFile = __DIR__ . '/data/Shalehi.json';
        $jsonData = file_get_contents($jsonFile);
        $dataArray = json_decode($jsonData, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            foreach ($dataArray as $data) {
                $employee = $this->employee($data);
                $customer = $this->customer($data);
                $item = $this->chalet($data, $category);
                $reservation = $this->reservation($data, $item, $employee, $customer);
                $this->invoices($data, $reservation, $employee, $customer);
            }
        } else {
            echo "Error decoding JSON: " . json_last_error_msg();
        }

    }
}
