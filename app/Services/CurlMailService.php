<?php

namespace App\Services;


class CurlMailService
{
    public static function send($to, $subject, $message)
    {
        $from = config('mail.from.address');
        $fromName = config('mail.from.name');
        $password = config('mailers.smtp.password');
        $username = config('mailers.smtp.username');
        $host = config('mailers.smtp.host');
        $port = config('mailers.smtp.port');


        $ch = curl_init();

        $emailData = <<<EOD
From: info@barq.solutions
To: eng.mg2011@gmail.com
Subject: Hello from Barq Solutions 2

This is a test email sent via cURL.
EOD;

        curl_setopt_array($ch, [
            CURLOPT_URL => 'smtp://' . $host . ':' . $port,
            CURLOPT_RETURNTRANSFER => true,

            // SMTP auth
            CURLOPT_USERNAME => $username,
            CURLOPT_PASSWORD => $password,

            // SSL / TLS
            CURLOPT_USE_SSL => CURLUSESSL_ALL,
            CURLOPT_SSL_VERIFYPEER => false,   // set true if server has valid cert
            CURLOPT_SSL_VERIFYHOST => false,

            // Mail settings
            CURLOPT_MAIL_FROM => $from,
            CURLOPT_MAIL_RCPT => [$to],

            // Email content
            CURLOPT_READFUNCTION => function ($ch, $fd, $length) use (&$emailData) {
                static $pos = 0;
                $chunk = substr($emailData, $pos, $length);
                $pos += strlen($chunk);
                return $chunk;
            },
            CURLOPT_UPLOAD => true,
        ]);

        $response = curl_exec($ch);

        if ($response === false) {
            throw new \Exception('cURL Error: ' . curl_error($ch));
        }

        curl_close($ch);

        return 'Email sent successfully';


    }

}
