<?php

namespace App\Service\Payment;

use App\Entity\Client\PaymentSettings;
use App\Entity\Customer\Customer;
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
     * @param Customer $customer
     * @param $cart
     * @return \App\Entity\Customer\Invoice
     * @throws \Throwable
     */
    public function customerPayment(Customer $customer, $cart)
    {
        if (isset($cart['shares']) || isset($cart['products'])) {
            $invoice = $this->manager->createCustomerInvoice($customer, $cart);

            $paymentMethods = PaymentSettings::getMethodsNames();

            if ($paymentMethods[$cart['method']] == 'card') {
                $merchant = $this->manager->getUSAePayMerchant($customer->getClient());

                if ($merchant) {
                    $transactionId = $this->USAePayService->customerPayment($merchant, $invoice, $cart['card']);
                    $this->manager->completeCustomerPayment($invoice, $transactionId);
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