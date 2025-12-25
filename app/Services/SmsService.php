<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class SmsService
{
    public function sendEnglish(string $mobile, string $message): array
    {
        return $this->send($mobile, $message, 1);
    }

    public function sendArabic(string $mobile, string $message): array
    {
        return $this->send($mobile, $message, 2);
    }

    private function send(string $mobile, string $message, int $language): array
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
}
