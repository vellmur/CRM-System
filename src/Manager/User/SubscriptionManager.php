<?php

namespace App\Manager\User;

use App\Entity\Customer\Merchant;
use App\Entity\ModuleAccess;
use App\Entity\Subscription;
use App\Entity\Client;
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
     * @param Client $client
     * @param Transaction $transaction
     * @param $usd
     * @return Subscription
     */
    public function createPayment(Client $client, Transaction $transaction, $usd)
    {
        $payment = new Subscription();

        $payment->setTransaction($transaction);
        $payment->setClient($client);
        $payment->setAmount($usd);

        $this->em->persist($payment);
        $this->em->flush();

        return $payment;
    }

    /**
     * @param Client $client
     * @param $modules
     * @param $paidPeriod
     */
    public function setAccess(Client $client, $modules, $paidPeriod)
    {
        // Get paid modules from last payment
        foreach ($modules as $moduleId)
        {
            $module = $this->em->getRepository(ModuleAccess::class)->find($moduleId);
            // Get client access info for this module
            $access = $this->em->getRepository(ModuleAccess::class)->findOrCreate($client, $module);

            // Count new client access period (Paid days + left days from previous payments)
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
     * @param Client $client
     * @return bool|mixed
     */
    public function getActiveTransaction(Client $client)
    {
        $transaction = $this->em->getRepository(Transaction::class)->getActiveTransaction($client);

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
     * @param Client $client
     * @param $moduleId
     * @return \App\Entity\ModuleAccess
     */
    public function getModuleAccess(Client $client, $moduleId)
    {
        $access = $this->em->getRepository('ModuleAccess.php')->findOneBy(['client' => $client, 'module' => $moduleId]);

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
     * @param Client $client
     * @return Merchant[]|array
     */
    public function getMerchants(Client $client)
    {
        return $this->em->getRepository(Merchant::class)->findBy(['client' => $client]);
    }
}