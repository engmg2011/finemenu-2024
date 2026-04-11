<?php

namespace App\Services;

//use SendGrid\Mail\Mail;

class OtpMailService
{
    public function send($to, $otp)
    {
//        $email = new Mail();
//        $email->setFrom(
//            config('services.sendgrid.from'),
//            config('services.sendgrid.name')
//        );
//        $email->setSubject("Your verification code");
//        $email->addTo($to);
//
//        $html = view('emails.otp', [
//            'otp' => $otp
//        ])->render();
//        $email->addContent("text/html", $html);
//
//        $sendgrid = new \SendGrid(config('services.sendgrid.key'));
//        $sendgrid->send($email);

        \Mail::send('emails.otp',['otp' => $otp], function ($message) use ($to) {
            $message->to($to)
                ->subject("Your verification code");
        });
        return 'Email sent!';

    }
}
