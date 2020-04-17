<?php

namespace App\Service\Payment;

use App\Entity\Transaction;

class TransactionService
{
    private $expiredTime;

    public function __construct($expiredTime)
    {
        $this->expiredTime = $expiredTime;
    }

    /**
     * @param $transaction
     * @return bool
     */
    public function checkExpired(Transaction $transaction)
    {
        $diffInMinutes = $this->getDiffInMinutes($transaction->getCreatedAt());

        if ($diffInMinutes >= $this->expiredTime) {
            $expired = true;
        } else {
            $expired = false;
        }

        return $expired;
    }

    /**
     * @param $transactionTime
     * @return bool
     */
    public function getDiffInMinutes($transactionTime)
    {
        $transactionCreated = strtotime(date_format($transactionTime, 'Y-m-d H:i:s'));
        $now = strtotime(date_format(new \DateTime(), 'Y-m-d H:i:s'));

        $diffInMinutes = round(abs($now - $transactionCreated) / 60.2);

        return $diffInMinutes;
    }

}