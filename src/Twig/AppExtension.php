<?php

namespace App\Twig;

use App\Entity\Customer\Address;
use App\Entity\Customer\Customer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Detection\MobileDetect;
use Twig\Extension\AbstractExtension;

class AppExtension extends AbstractExtension
{
    private $translator;

    private $router;

    /**
     * AppExtension constructor.
     * @param TranslatorInterface $translator
     * @param UrlGeneratorInterface $router
     */
    public function __construct(TranslatorInterface $translator, UrlGeneratorInterface $router)
    {
        $this->translator = $translator;
        $this->router = $router;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('dayOfWeek', [$this, 'dayOfWeek']),
            new TwigFilter('usdtobtc', [$this, 'convertUSDtoBTC']),
            new TwigFilter('satoshitobtc', [$this, 'convertSatoshiToBTC']),
            new TwigFilter('satoshitousd', [$this, 'convertSatoshitoUSD']),
            new TwigFilter('moduleName', [$this, 'nameOfModule']),
            new TwigFilter('json_decode', [$this, 'json_decode']),
            new TwigFilter('address_format', [$this, 'address_format']),
            new TwigFilter('profile_link', [$this, 'getProfileLink']),
            new TwigFilter('get_end_time', [$this, 'calculateEndTime']),
            new TwigFilter('add_read_more', [$this, 'addReadMore']),
            new TwigFilter('media_public_url', [$this, 'getMediaPublicUrl']),
            new TwigFilter('file_size', [$this, 'formatSizeUnits'])
        ];
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new TwigFunction('is_mobile', [$this, 'isMobile']),
        ];
    }

    /**
     * Get datetime object from start time (in hours) and duration (in hours)
     *
     * @param $start
     * @param $duration
     * @return \DateTime|null
     * @throws \Exception
     */
    public function calculateEndTime($start, $duration)
    {
        if ($start && $duration) {
            $date = date("Y:m:d H:i:s", strtotime($start));
            $datetime = new \DateTime($date);
            $datetime->modify('+ ' . $duration . ' hours');

            return $datetime;
        } else {
            return null;
        }
    }

    /**
     * @param $string
     * @return mixed
     */
    public function json_decode($string)
    {
        return json_decode($string, true);
    }

    /**
     * @param $usd
     * @return mixed
     */
    public function convertUSDtoBTC($usd)
    {
        $request = 'https://blockchain.info/tobtc?currency=USD&value=' . $usd;

        return $this->requestToApi($request);
    }

    public function nameOfModule($id)
    {
        $modules = [
            '1' => 'Crops',
            '2' => 'Customers',
            '3' => 'Company'
        ];

        return $modules[$id];
    }

    public function dayOfWeek($numOfDay)
    {
        $week = [
            '1' => $this->translator->trans('monday', [], 'choices'),
            '2' => $this->translator->trans('tuesday', [], 'choices'),
            '3' => $this->translator->trans('wednesday', [], 'choices'),
            '4' => $this->translator->trans('thursday', [], 'choices'),
            '5' => $this->translator->trans('friday', [], 'choices'),
            '6' => $this->translator->trans('saturday', [], 'choices'),
            '7' => $this->translator->trans('sunday', [], 'choices')
        ];
        
        return $week[$numOfDay];
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

    /**
     * @param $satoshi
     * @return string
     */
    public function convertSatoshiToBTC($satoshi)
    {
        $value = sprintf('%.8f',  ($satoshi / 100000000));
        return rtrim($value, '0');
    }

    /**
     * @return mixed
     */
    public function convertSatoshitoUSD($satoshi)
    {
        $btcPrice = $this->getBtcAveragePrice();
        $satoshiInBtc = $satoshi / 100000000;
        $oneSatoshiPrice =  $btcPrice / 100000000;

        return round(($satoshiInBtc * $oneSatoshiPrice) * 100000000, 3);
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
     * Convert address to needed format with tags.
     *
     * First line of address is Street Apartment
     * Second line of address is Postal code, City State
     *
     * @param Address|array $address
     * @return string
     */
    public function address_format($address)
    {
        // Get data form Address object or from array
        if (is_object($address)) {
            $street = $address->getStreet();
            $apartment = $address->getApartment();
            $postalCode = $address->getPostalCode();
            $region = $address->getRegion();
            $city = $address->getCity();
        } else {
            $street = $address['street'];
            $apartment = $address['apartment'];
            $postalCode = $address['postalCode'];
            $region = $address['region'];
            $city = $address['city'];
        }

        $formattedAddress = '<span class="address-details">' . $street;

        if ($apartment) $formattedAddress .= ' ' . $apartment;

        $formattedAddress .= '</span><br/><span class="address-region">';

        if ($postalCode) $formattedAddress .= $postalCode;

        if ($region || $city) {
            $formattedAddress .= ', ' . $city . ' ' . $region;
        }

        $formattedAddress .= '</span>';

        return $formattedAddress;
    }

    /**
     * @param Customer $customer
     * @return string
     */
    public function getProfileLink(Customer $customer)
    {
        $user = $customer->getClient()->getOwner();

        // Generate absolute url
        $link = $this->router->generate('membership_profile', [
            '_locale' => $user->getLocale()->getId() ? $user->getLocale()->getCode() : 'en',
            'token' => $customer->getToken()
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        // If server is not localhost, change scheme to https
        if (!strstr($link, '127.0.0.1') && !strstr($link, 'blackdirt.local')) {
            $link = str_replace('http:', 'https:', $link);
        }

        return $link;
    }

    /**
     * @return bool
     */
    function isMobile()
    {
        $detect = new MobileDetect();
        return $detect->isMobile();
    }

    /**
     * @param $text
     * @return string
     */
    public function addReadMore($text)
    {
        $visibleWordsNum = 96;
        $textToAdd = '<span class="text-dots">...</span><span class="read-more">';
        $words = explode(' ',$text,$visibleWordsNum + 1);
        $lastWord = array_pop($words);

        $croppedText = '<div class="text-read-block">' .
                implode(" ", $words) . ' ' . $textToAdd . ' ' . $lastWord . '</span>
                <button type="button" class="btn-link read-more-btn">Show more</button>
            </div>';

        return $croppedText;
    }

    /**
     * @param $bytes
     * @return string
     */
    function formatSizeUnits($bytes)
    {
        if ($bytes >= 1073741824) {
            $bytes = number_format($bytes / 1073741824, 2) . ' Gb';
        } elseif ($bytes >= 1048576) {
            $bytes = number_format($bytes / 1048576, 2) . ' Mb';
        } elseif ($bytes >= 1024) {
            $bytes = number_format($bytes / 1024, 2) . ' Kb';
        } elseif ($bytes > 1) {
            $bytes = $bytes . ' bytes';
        } elseif ($bytes == 1) {
            $bytes = $bytes . ' byte';
        } else {
            $bytes = '0 bytes';
        }

        return $bytes;
    }

    public function getName()
    {
        return 'app_extension';
    }
}