<?php

namespace App\Http\Controllers;

use QrCode;
use Storage;

class QrCodeController extends Controller
{
    public function index()
    {
        return view('qrcode');
    }
    public function save()
    {
        $image = QrCode::format('png')
//            ->merge('img/t.jpg', 0.1, true)
            ->size(200)->errorCorrection('H')
            ->generate('A simple example of QR code!');
        $output_file = '/public/qr-codes/img-' . time() . '.png';
        Storage::disk('local')->put($output_file, $image);
        return $output_file;
    }
}
