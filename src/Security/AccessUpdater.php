<?php

namespace App\Security;

use App\Entity\Client\Client;
use Doctrine\ORM\EntityManagerInterface;

class AccessUpdater
{
    private $em;

    CONST TRIAL_DAYS = 14;

    /**
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * Update module status.
     *
     * Status is based on current status and amount of days left to the module expiration date.
     *
     * PENDING: is the trial period. Trial period defines by TRIAL_DAYS. Sets first time after client Sign up.
     * ACTIVE: Client module is in ACTIVE status, if to the expiration date left more than TRIAL_DAYS.
     * RENEWAL: Module is in RENEWAL status, if module is ACTIVE and to the expiration date left less than TRIAL_DAYS.
     * LAPSED: Client module is in LAPSED status, if to the expiration date left 0 days.
     *
     * @param Client $client
     * @return bool
     * @throws \Exception
     */
    public function updateModulesAccess(Client $client)
    {
        // Flag for saving result of changing statuses
        $isAccessUpdated = false;

        foreach ($client->getAccesses() as $access) {
            $daysLeft = $this->countDaysLeft($access->getExpiredAt());

            // Is module in trial mode and its not expired yet, nothing to do
            if ($access->getStatusName() == 'PENDING' && $daysLeft > 0 && $daysLeft < self::TRIAL_DAYS) {
                break;
            }

            $currentModuleStatus = $access->getStatus();

            if ($daysLeft == 0) {
                if ($access->getStatusName() != 'LAPSED') {
                    $access->setStatusByName('LAPSED');
                }
            } else {
                if ($daysLeft > self::TRIAL_DAYS && $access->getStatusName() != 'ACTIVE' ) {
                    $access->setStatusByName('ACTIVE');
                } elseif ($daysLeft <= self::TRIAL_DAYS && $access->getStatusName() != 'RENEWAL') {
                    $access->setStatusByName('RENEWAL');
                }
            }

            if (!$isAccessUpdated && $currentModuleStatus != $access->getStatus()) $isAccessUpdated = true;
        }

        if ($isAccessUpdated) $this->em->flush();

        return $isAccessUpdated;
    }

    /**
     * @param $date
     * @return false|float|int
     * @throws \Exception
     */
    private function countDaysLeft($date)
    {
        $now = strtotime(date_format(new \DateTime(), 'Y-m-d H:i:s'));
        $expiredAt = strtotime(date_format($date, 'Y-m-d H:i:s'));

        $diffInDays = floor(($expiredAt - $now) / (60 * 60 * 24)) + 1;

        if ($diffInDays < 0) $diffInDays = 0;

        return $diffInDays;
    }
}