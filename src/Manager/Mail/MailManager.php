<?php

namespace App\Manager\Mail;

use App\Entity\Customer\Email\EmailRecipient;
use App\Entity\Master\Email\Recipient;
use Doctrine\ORM\EntityManagerInterface;

class MailManager
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param int $recipientId
     * @param string $recipientType
     * @throws \Exception
     */
    public function setAsOpened(int $recipientId, string $recipientType)
    {
        $repository = $recipientType == 'building' ? $this->em->getRepository(Recipient::class)
            : $this->em->getRepository(EmailRecipient::class);

        $recipient = $repository->findOneBy(['id' => $recipientId]);

        if (!$recipient) {
            throw new \Exception('Recipient can`t be found.');
        }

        if (!$recipient->isOpened()) {
            $recipient->setIsOpened(true);
            $recipient->setIsBounced(false);

            $this->em->flush();
        }
    }
}