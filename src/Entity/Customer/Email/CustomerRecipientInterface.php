<?php

namespace App\Entity\Customer\Email;

use App\Entity\Customer\Customer;

interface CustomerRecipientInterface
{
    /**
     * @return Customer
     */
    public function getCustomer() : Customer;

    /**
     * @param Customer $customer
     * @return mixed
     */
    public function setCustomer(Customer $customer);

    /**
     * @return mixed
     */
    public function getFeedback();

    /**
     * @param Feedback $feedback
     * @return mixed
     */
    public function setFeedback(Feedback $feedback);
}
