<?php

namespace App\Service;

use App\Service\Data\CountryInfo;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class LocationService
 * @package App\Service
 */
class LocationService
{
    private $geonamesLogin;

    private $countriesInfo;

    private $token_storage;

    public function __construct($geonamesLogin, TokenStorageInterface $storage)
    {
        $this->geonamesLogin = $geonamesLogin;
        $this->token_storage = $storage;
        $this->countriesInfo = new CountryInfo();
    }

    /**
     * Returns list of all timezones supported by PHP
     * @param string|null $countryCode
     * @return array
     */
    public function getTimezonesList(?string $countryCode = null)
    {
        $timezones = [];

        $zones = $countryCode && $countryCode != ''
            ? timezone_identifiers_list(\DateTimeZone::PER_COUNTRY, strtoupper($countryCode))
            : timezone_identifiers_list();

        foreach ($zones as $key => $zone) {
            $zoneExploded = explode('/', $zone); // 0 => Continent, 1 => City

            // Only use "friendly" continent names
            if ($zoneExploded[0] == 'Africa' || $zoneExploded[0] == 'America' || $zoneExploded[0] == 'Antarctica'
                || $zoneExploded[0] == 'Arctic' || $zoneExploded[0] == 'Asia' || $zoneExploded[0] == 'Atlantic'
                || $zoneExploded[0] == 'Australia' || $zoneExploded[0] == 'Europe' || $zoneExploded[0] == 'Indian'
                || $zoneExploded[0] == 'Pacific')
            {
                if (isset($zoneExploded[1]) != '') {
                    $offset = (new \DateTimeZone($zone))->getOffset(new \DateTime);
                    $offsetPrefix = $offset < 0 ? '-' : '+';
                    $offsetFormatted = gmdate('H:i', abs($offset));
                    $utcOffset = '(UTC' . $offsetPrefix . $offsetFormatted . ') ' . $zone;

                    $timezones[$zoneExploded[0]][$utcOffset] = $zone; // Creates array(DateTimeZone => 'Friendly name')
                }
            }
        }

        return $timezones;
    }
}