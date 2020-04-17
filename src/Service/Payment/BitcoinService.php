<?php

namespace App\Service\Payment;

class BitcoinService
{
    // Bitcoin transactions info
    private $wallet;

    private $confirmations;

    private $fee;

    private $callback;
    
    public function __construct($wallet)
    {
        $this->wallet = $wallet;
        $this->confirmations = 3;
        $this->fee = 'medium';

        //For dev environment mode
        if (!stristr($_SERVER['HTTP_HOST'], 'localhost')) {
            $this->callback = 'http://' . $_SERVER['HTTP_HOST'] . '/transaction/confirm/';
        } else {
            $this->callback = 'http://www.test.me/addbalance.php?user=';
        }
    }


    /**
     * @param $clientId
     * @return mixed
     */
    public function createInvoice($clientId)
    {
        $request = "https://bitaps.com/api/create/payment/". $this->wallet . "/" . urlencode($this->callback . $clientId) . "?confirmations=" . $this->confirmations . "&fee_level=" . $this->fee;
        $response = $this->requestToApi($request);
        
        return $response;
    }

    /**
     * @return mixed
     */
    public function getBtcAveragePrice()
    {
        $request = "https://bitaps.com/api/ticker/average";
        $response = $this->requestToApi($request);

        return $response->usd;
    }

    /**
     * @return mixed
     */
    public function convertUSDtoBTC($usd)
    {
        $request = 'https://blockchain.info/tobtc?currency=USD&value=' . $usd;
        $response = $this->requestToApi($request);

        return $response;
    }

    /**
     * @param $btc
     * @return mixed
     */
    public function convertBTCtoUSD($btc)
    {
        $request = 'https://apiv2.bitcoinaverage.com/convert/global?from=BTC&to=USD&amount=' . $btc;
        $response = $this->requestToApi($request);

        return $response->price;
    }


    /**
     * @return mixed
     */
    public function convertSatoshitoUSD($satoshi)
    {
        $btcPrice = $this->getBtcAveragePrice();
        $satoshiInBtc = $satoshi / 100000000;
        $oneSatoshiPrice =  $btcPrice / 100000000;

        $usd = round(($satoshiInBtc * $oneSatoshiPrice) * 100000000, 3);

        return $usd;
    }

    /**
     * @param $satoshi
     * @return string
     */
    public function convertFromSatoshiToBTC($satoshi)
    {
        $value = $satoshi / 100000000 ;

        $value = sprintf('%.8f', $value);
        $btc = rtrim($value, '0');

        return $btc;
    }

    public function convertUsdToSatoshi($usd)
    {
        $btc = $this->convertUSDtoBTC($usd);
        $amount = $this->convertBTCToSatoshi($btc);

        return $amount;
    }

    /**
     * @param $btc
     * @return mixed
     */
    public function convertBTCToSatoshi($btc)
    {
        $satoshi = $btc * (pow(10, 8));

        return $satoshi;
    }

    /**
     * @param $url
     * @return mixed
     */
    function requestToApi($url)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt( $ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

        $result = curl_exec($ch);

        curl_close($ch);

        return json_decode($result);
    }
}