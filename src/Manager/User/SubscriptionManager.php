<?php

namespace App\Manager\User;

use App\Entity\Customer\Merchant;
use App\Entity\ModuleAccess;
use App\Entity\Subscription;
use App\Entity\Building;
use App\Entity\PaymentMethod;
use App\Entity\Transaction;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;

class SubscriptionManager
{
    private $em;

    private $paymentRepository;

    /**
     * SubscriptionManager constructor.
     * @param EntityManagerInterface $em
     * @param PaymentRepository $paymentRepository
     */
    public function __construct(EntityManagerInterface $em, PaymentRepository $paymentRepository)
    {
        $this->em = $em;
        $this->paymentRepository = $paymentRepository;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * @param Building $building
     * @param Transaction $transaction
     * @param $usd
     * @return Subscription
     */
    public function createPayment(Building $building, Transaction $transaction, $usd)
    {
        $payment = new Subscription();

        $payment->setTransaction($transaction);
        $payment->setBuilding($building);
        $payment->setAmount($usd);

        $this->em->persist($payment);
        $this->em->flush();

        return $payment;
    }

    /**
     * @param Building $building
     * @param $modules
     * @param $paidPeriod
     */
    public function setAccess(Building $building, $modules, $paidPeriod)
    {
        // Get paid modules from last payment
        foreach ($modules as $moduleId)
        {
            $module = $this->em->getRepository(ModuleAccess::class)->find($moduleId);
            // Get building access info for this module
            $access = $this->em->getRepository(ModuleAccess::class)->findOrCreate($building, $module);

            // Count new building access period (Paid days + left days from previous payments)
            $paidPeriod += $this->countDaysLeft($access->getExpiredAt());

            $expiredAt = $this->getDateAfterDays($paidPeriod);

            $status = $this->em->getRepository(ModuleAccess::class)->findOneBy(['name' => 'active']);

            $access->setExpiredAt($expiredAt);
            $access->setStatus($status);
            $access->setUpdatedAt(new \DateTime());
            $this->em->persist($access);
        }

        $this->em->flush();
    }

    /**
     * @param $date
     * @return mixed
     */
    public function countDaysLeft($date)
    {
        $now = strtotime(date_format(new \DateTime(), 'Y-m-d H:i:s'));
        $expiredAt = strtotime(date_format($date, 'Y-m-d H:i:s'));

        $diffInDays = floor(($expiredAt - $now) / (60 * 60 * 24));

        if ($diffInDays < 0) $diffInDays = 0;

        return $diffInDays;
    }

    /**
     * @param $days
     * @return \DateTime
     */
    public function getDateAfterDays($days)
    {
        $date = new \DateTime();

        $date->modify('+' . $days .' day');

        return $date;
    }

    /**
     * @param Building $building
     * @return bool|mixed
     */
    public function getActiveTransaction(Building $building)
    {
        $transaction = $this->em->getRepository(Transaction::class)->getActiveTransaction($building);

        return $transaction;
    }

    /**
     * @param $name
     * @return PaymentMethod
     */
    public function getMethod($name)
    {
        return $this->em->getRepository(PaymentMethod::class)->findOneBy(['name' => $name]);
    }

    /**
     * @param Building $building
     * @param $moduleId
     * @return \App\Entity\ModuleAccess
     */
    public function getModuleAccess(Building $building, $moduleId)
    {
        $access = $this->em->getRepository('ModuleAccess.php')->findOneBy(['building' => $building, 'module' => $moduleId]);

        return $access;
    }

    /**
     * @param Merchant $merchant
     */
    public function createMerchant(Merchant $merchant)
    {
        $this->em->persist($merchant);
        $this->em->flush();
    }

    /**
     *
     */
    public function updateMerchant()
    {
        $this->em->flush();
    }

    /**
     * @param Merchant $merchant
     */
    public function removeMerchant(Merchant $merchant)
    {
        $this->em->remove($merchant);
        $this->em->flush();
    }

    /**
     * @param Building $building
     * @return Merchant[]|array
     */
    public function getMerchants(Building $building)
    {
        return $this->em->getRepository(Merchant::class)->findBy(['building' => $building]);
    }
}