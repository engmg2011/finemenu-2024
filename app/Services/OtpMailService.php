<?php

namespace App\Services;

use SendGrid\Mail\Mail;

class OtpMailService
{
    public function send($to, $otp)
    {
        $email = new Mail();
        $email->setFrom(
            config('services.sendgrid.from'),
            config('services.sendgrid.name')
        );
        $email->setSubject("Your verification code");
        $email->addTo($to);
        $email->addContent(
            "text/html",
            "<p>Your OTP code is:</p>
             <h2>{$otp}</h2>
             <p>This code expires in 5 minutes.</p>"
        );

        $sendgrid = new \SendGrid(config('services.sendgrid.key'));
        $sendgrid->send($email);
    }
}
