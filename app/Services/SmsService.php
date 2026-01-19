<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Twilio\Rest\Client;

class SmsService
{
    protected Client $client;

    public function sendSMS(string $mobile, string $message)
    {
        if(config('services.sms.medium') == 'KwtSms')
            $this->sendByKwtSms($mobile, $message);
        else
            $this->sendByTwilio($mobile, $message);
    }

    private function sendByKwtSms(string $mobile, string $message, int $language = 1)
    {
        $response = Http::get(config('services.sms.url'), [
            'apikey'  => config('services.sms.key'),
            'language'=> $language,
            'sender'  => config('services.sms.sender'),
            'mobile'  => $mobile,
            'message' => $message,
        ]);
        return [
            'success' => $response->successful(),
            'status'  => $response->status(),
            'body'    => $response->body(),
        ];
    }

    public function sendByTwilio(string $mobile, string $code){
        $this->client = new Client(
            str_replace("bbbb", "bbb", config('services.twilio.sid')),
            str_replace("ccc5", "cc5", config('services.twilio.token'))
        );
        $this->client->messages->create(
            $mobile,
            [
                'from' => config('services.twilio.from'),
                'body' => "Your OTP code is {$code}. Do not share it."
            ]
        );
    }
}
