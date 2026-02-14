<?php


namespace App\Services;


use App\Models\Branch;
use App\Models\Item;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use QrCode;


class QrService
{

    public function generateBase64QrCode(string $content)
    {

        // Create a renderer with SVG backend and style
        $renderer = new ImageRenderer(
            new RendererStyle(400 , 0), // 400 is the size of the QR code
            new SvgImageBackEnd()
        );

        // Instantiate the Writer with the renderer
        $writer = new Writer($renderer);


        // Write the QR code to a file
        $qrCode = $writer->writeString($content);

        return 'data:image/svg+xml;base64,' . base64_encode($qrCode);

    }


    /**
     * Generate QR code with user preferences
     *
     * @param Branch $branch
     * @param string $content
     * @param int $resolution
     * @param float $logoSize
     * @param string|null $logoPath
     * @return mixed
     */
    public function generateBranchQrCode(Branch $branch, string $content,
                                         int    $resolution = 500, float $logoSize = 0.3,
                                         string $logoPath = null)
    {

        // Create a renderer with SVG backend and style
        $renderer = new ImageRenderer(
            new RendererStyle(400), // 400 is the size of the QR code
            new SvgImageBackEnd()
        );

        // Instantiate the Writer with the renderer
        $writer = new Writer($renderer);


        // Write the QR code to a file
        $qrCode = $writer->writeString($content);

        // Output the QR code as an SVG
        header('Content-Type: image/svg+xml');
        echo $qrCode;



        //        $qrImage = QrCode::encode($content, 'UTF-8')
//            ->errorCorrection('H')
////            ->style($branch->setting->qr_shape)
//            ->format('png')
//            ->size($resolution);

//        $imageName = $branch->setting->getOriginal('qr_logo_image');
//        $qrLogo = '/storage/app/qr/'.$imageName;
//
//        if($branch->setting->isQrGradient()){
//            [$r_s, $g_s, $b_s] = sscanf($branch->setting->qr_color, "#%02x%02x%02x");
//            [$r_e, $g_e, $b_e] = sscanf($branch->setting->qr_secondary_color, "#%02x%02x%02x");
//            $qrImage = $qrImage->gradient($r_s, $g_s, $b_s, $r_e, $g_e, $b_e, $branch->setting->qr_gradient_type);
//        }
//        else{
//            $qrColor = empty($branch->setting->qr_color) ? '#000000' : $branch->setting->qr_color;
//            [$r, $g, $b] = sscanf($qrColor, "#%02x%02x%02x");
//            $qrImage = $qrImage->color($r, $g, $b);
//        }
//
//        [$r, $g, $b] = sscanf($branch->setting->qr_background_color, "#%02x%02x%02x");
//        $qrImage = $qrImage->backgroundColor($r, $g, $b);
//
//        if(!is_null($logoPath)){
//            $qrImage = $qrImage->merge($logoPath, $logoSize, true);
//        }
//        elseif($branch->setting->customized_qr_enabled && !empty($imageName) && File::exists(base_path($qrLogo))){
//            $qrImage = $qrImage->merge($qrLogo, $logoSize);
//        }

//        return $qrImage->generate();
    }

    /**
     * Generate QR code that decode a string value
     *
     * @param string $content
     * @param int $resolution
     * @return mixed
     */
    public function generateQrCode(string $content, int $resolution = 500)
    {
        return QrCode::encode($content, 'UTF-8')
            ->format('png')
            ->size($resolution)->errorCorrection('H')
            ->generate();
    }

    public function generateItemQrCode(string $content)
    {

        // Create a renderer with SVG backend and style
        $renderer = new ImageRenderer(
            new RendererStyle(400), // 400 is the size of the QR code
            new SvgImageBackEnd()
        );

        // Instantiate the Writer with the renderer
        $writer = new Writer($renderer);


        // Write the QR code to a file
        $qrCode = $writer->writeString($content);

        // Output the QR code as an SVG
        header('Content-Type: image/svg+xml');
        echo $qrCode;
    }



}
