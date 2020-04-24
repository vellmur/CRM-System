<?php

namespace App\Service\Mail;

class MailService
{
    /**
     * @return false|string
     */
    public function renderTransparentImage()
    {
        $image = imagecreatetruecolor(1,1);
        imagefill($image, 0, 0, 0xDDDDD);

        ob_start();
        imagepng($image);
        $imageString = ob_get_clean();

        return $imageString;
    }
}