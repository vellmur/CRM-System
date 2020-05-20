<?php

namespace App\Service\Payment;

use App\Entity\Building\PaymentSettings;
use App\Entity\Owner\Owner;
use App\Manager\Payment\PaymentManager;
use App\Service\Payment\Gateway\USAePayService;

class PaymentService
{
    public $manager;

    private $USAePayService;

    /**
     * SubscriptionService constructor.
     * @param PaymentManager $manager
     * @param USAePayService $USAePayService
     */
    public function __construct(PaymentManager $manager, USAePayService $USAePayService)
    {
        $this->manager = $manager;
        $this->USAePayService = $USAePayService;
    }

    /**
     * @param Owner $owner
     * @param $cart
     * @return \App\Entity\Owner\Invoice
     * @throws \Throwable
     */
    public function ownerPayment(Owner $owner, $cart)
    {
        if (isset($cart['shares']) || isset($cart['products'])) {
            $invoice = $this->manager->createOwnerInvoice($owner, $cart);

            $paymentMethods = PaymentSettings::getMethodsNames();

            if ($paymentMethods[$cart['method']] == 'card') {
                $merchant = $this->manager->getUSAePayMerchant($owner->getBuilding());

                if ($merchant) {
                    $transactionId = $this->USAePayService->ownerPayment($merchant, $invoice, $cart['card']);
                    $this->manager->completeOwnerPayment($invoice, $transactionId);
                } else {
                    throw new \Exception('Merchant settings are not configured!');
                }
            }

            return $invoice;
        } else {
            throw new \Exception('Cart is empty or payment information is not correct!');
        }
    }
}