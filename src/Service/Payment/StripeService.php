<?php

namespace App\Service\Payment;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\Charge;

use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\FormFactoryInterface;

use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class StripeService
{
    private $secretKey;

    private $publicKey;
    
    private $formBuilder;

    
    public function __construct($secretKey, $publicKey, FormFactoryInterface $formFactory)
    {
        Stripe::setApiKey($secretKey);

        $this->secretKey = $secretKey;
        $this->publicKey = $publicKey;
        
        $this->formBuilder = $formFactory;
    }

    public function createCustomer($token, $email)
    {
        $customer = Customer::create(array(
            'email' => $email,
            'card'  => $token
        ));

        return $customer->id;
    }

    public function createCharge($token, $email, $amount)
    {
        //$customerId = $this->createCustomer($token, $email);

        $cents = $this->convertDollarsToCents($amount);

        //charge for user ads
        $charge = Charge::create(array(
            'amount'   => $cents,
            'currency' => 'usd',
            'source' => $token,
            'description' => 'Black Dirt Software. Client: ' . $email
        ));

        
       return $charge;
    }

    public function convertDollarsToCents($dollars)
    {
        $cents = $dollars * 100;

        return $cents;
    }

    public function convertCentsToDollars($cents)
    {
        $dollars = number_format(($cents /100), 2, '.', ' ');

        return $dollars;
    }
}