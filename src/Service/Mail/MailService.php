<?php

namespace App\Service\Mail;

use App\Entity\Owner\Email\AutoEmail;
use App\Entity\Email\EmailLogInterface;
use Doctrine\Common\Collections\Collection;

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

    /**
     * @param $recipients
     * @return array
     */
    public function getMailRecipientsStats(array $recipients)
    {
        $recipientsStats = [
            'sent' => [],
            'delivered' => [],
            'opened' => [],
            'clicked' => [],
            'failed' => [],
            'qty' => [
                'sent' => 0,
                'delivered' => 0,
                'opened' => 0,
                'clicked' => 0,
                'failed' => 0
            ]
        ];

        // Sort list of recipients by email status
        foreach ($recipients as $recipient) {
            if ($recipient->isSent()) {
                $recipientsStats['sent'][] = $recipient;
                $recipientsStats['qty']['sent']++;
            } else {
                $recipientsStats['failed'][] = $recipient;
                $recipientsStats['qty']['failed']++;
            }

            if ($recipient->isDelivered()) {
                $recipientsStats['delivered'][] = $recipient;
                $recipientsStats['qty']['delivered']++;
            }

            if ($recipient->isOpened()) {
                $recipientsStats['opened'][] = $recipient;
                $recipientsStats['qty']['opened']++;
            }

            if ($recipient->isClicked()) {
                $recipientsStats['clicked'][] = $recipient;
                $recipientsStats['qty']['clicked']++;
            }
        }

        $recipientsStats['total'] = count($recipients);

        return $recipientsStats;
    }
}