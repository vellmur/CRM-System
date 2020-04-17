<?php

namespace App\Service\Payment;

use App\Entity\Subscription;
use App\Manager\Payment\SubscriptionManager;
use App\Manager\Payment\TransactionManager;

use App\Entity\PaymentMethod;
use App\Entity\Client\Client;
use App\Service\Payment\Gateway\USAePayService;

class SubscriptionService
{
    public $manager;

    private $btcService;

    private $cardService;

    private $USAePayService;

    private $transactionService;

    private $transactionManager;

    /**
     * SubscriptionService constructor.
     * @param SubscriptionManager $manager
     * @param TransactionService $transactionService
     * @param TransactionManager $transactionManager
     * @param BitcoinService $btcService
     * @param StripeService $stripeService
     * @param USAePayService $USAePayService
     */
    public function __construct(SubscriptionManager $manager, TransactionService $transactionService, TransactionManager $transactionManager, BitcoinService $btcService, StripeService $stripeService, USAePayService $USAePayService)
    {
        $this->manager = $manager;
        $this->transactionManager = $transactionManager;

        $this->btcService = $btcService;
        $this->cardService = $stripeService;
        $this->USAePayService = $USAePayService;

        $this->transactionService = $transactionService;
    }

    /**
     * @param Client $client
     * @param $modules
     * @return \App\Entity\Transaction
     */
    public function createBitcoinPayment(Client $client, $method, $modules)
    {
        $response = $this->btcService->createInvoice($client->getId());

        $transaction = null;

        if (property_exists($response,'address')) {
            $amount = $this->getPaymentAmount($method, $client->getLevelName(), $modules);

            $satoshi = $this->btcService->convertUsdToSatoshi($amount);

            $transaction = $this->transactionManager->createTransaction($client, $method, $satoshi, $modules, $response->address, $response->invoice, $response->payment_code);
        }

        return $transaction;
    }

    /**
     * @param PaymentMethod $method
     * @param $level
     * @param $modules
     * @return int
     */
    public function getPaymentAmount(PaymentMethod $method, $level, $modules)
    {
        $amount = 0;

        for ($i = 0; $i < count($modules); $i++) {
            $amount += ($level == 'Farmer' ? $method->getFarmerPrice() : $method->getGardenerPrice());
        }

        return $amount;
    }

    /**
     * @param $client
     * @param $modules
     * @param $token
     * @param $user
     * @return Subscription
     */
    public function createCardPayment(Client $client, $modules, $token, $user)
    {
        $method = $this->manager->getMethod('card');
        $amount = $this->getPaymentAmount($method, $client->getLevelName(), $modules);
        
        $response = $this->cardService->createCharge($token, $user, $amount);

        $transaction = $this->transactionManager->createConfirmedTransaction($client, $method, $response->amount, $modules, $response->source->id, $token, $response->id);

        $usd = $this->cardService->convertCentsToDollars($response->amount);
        
        $payment = $this->manager->createPayment($client, $transaction, $usd);

        return $payment;
    }

    /**
     * @param $period
     * @param $price
     * @param $amount
     * @return mixed
     */
    public function getPaidPeriod($period, $price, $amount)
    {
        $dayPrice = $price / $period;

        $paidPeriod = round($amount / $dayPrice);

        return $paidPeriod ;
    }

    /**
     * @param Client $client
     * @param $code
     * @param $amount
     * @return string
     */
     public function confirmTransaction(Client $client, $code, $amount)
     {
         $transaction = $this->transactionManager->findTransactionByPaymentCode($client, $code);
         
         $confirmedTransaction = $this->transactionManager->confirmTransaction($transaction, $amount);

         $usd = $this->btcService->convertSatoshitoUSD($amount);

         $payment = $this->manager->createPayment($client, $confirmedTransaction, $usd);

         $this->updateAccess($client, $payment);
         
         return $confirmedTransaction->getInvoice();
     }

    /**
     * @param $transaction
     * @return mixed
     */
    public function cancelTransaction($transaction)
    {
        $status = $this->transactionManager->getStatus('canceled');
        $cancel = $this->transactionManager->cancelTransaction($transaction, $status);

        return $cancel;
    }

    /**
     * @param $transaction
     * @return bool|mixed
     */
    public function cancelIfExpired($transaction)
    {
        $expired = $this->transactionService->checkExpired($transaction);

        $canceled = false;

        if ($expired) {
            $status = $this->transactionManager->getStatus('expired');
            $canceled = $this->transactionManager->cancelTransaction($transaction, $status);
        }

        return $canceled;
    }

    /**
     * @param Client $client
     * @param Subscription $payment
     * @return bool
     */
    public function updateAccess(Client $client, Subscription $payment)
    {
        $method = $payment->getTransaction()->getMethod();
        $modules = $payment->getTransaction()->getModules();

        // Count how much money will go for one module
        $moduleMoney = $payment->getAmount() / count($modules);

        $price = ($client->getLevelName() == 'Farmer' ? $method->getFarmerPrice() : $method->getGardenerPrice());

        // Get client paid period
        $paidPeriod = $this->getPaidPeriod(365, $price, $moduleMoney);

        $this->manager->setAccess($client, $modules, $paidPeriod);

        return true;
    }
}