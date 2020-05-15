<?php

namespace App\Service;

use App\Data\CountryInfo;
use MenaraSolutions\Geographer\Country;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class LocationService
 *
 * Service for locations data. Data from geonames API.
 *
 */
class LocationService
{
    private $geonamesUrl = 'http://api.geonames.org';

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
     *
     * @param $countryCode
     * @return array
     */
    public function getTimezonesList($countryCode)
    {
        $timezones = [];

        $zones = $countryCode ? timezone_identifiers_list(\DateTimeZone::PER_COUNTRY, $countryCode) : timezone_identifiers_list();

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


    /**
     * @param $countryCode
     * @return mixed
     */
    public function getCountryInfo($countryCode)
    {
        return $this->countriesInfo->getCountryInfo($countryCode);
    }

    /**
     * @return mixed
     */
    public function getBuildingCountry()
    {
        $user = $this->token_storage->getToken()->getUser();

        $country = is_object($user) ? $user->getBuilding()->getCountry() : null;

        return $country;
    }


    /**
     * @param $countryCode
     * @param $postalCode
     * @return array|null
     */
    public function getGeoByPostalCode($countryCode, $postalCode)
    {
        $query = $this->getGeonamesQuery([
            'country' => $countryCode,
            'postalcode' => str_replace(' ','', $postalCode
            )], 'postalCodeSearch');

        $result = $this->getDataFromApi($query);

        $data = null;

        // Get found places for each postalCode
        if ($result && count($result->postalCodes) > 0) {
            foreach ($result->postalCodes as $place) {
                $adminCode = isset($place->adminCode1) ? $place->adminCode1 : null;

                // Second query to API, because first not return geonameId, but second can do this
                $places = $this->getGeoPlaces($countryCode, $place->placeName, $adminCode);

                // Write into array resulted places
                if ($places && $places->totalResultsCount > 0) {
                    foreach ($places->geonames as $geoname) {
                        $data['cities'][$geoname->name] = $geoname->geonameId;
                    }

                    $data['region']['name'] = $places->geonames[0]->adminName1;
                }
            }
        }

        return $data;
    }

    /**
     * @param $countryCode
     * @param $placeName
     * @param null $adminCode
     * @return mixed
     */
    public function getGeoPlaces($countryCode, $placeName, $adminCode = null)
    {
        $params = ['country' => $countryCode, 'name' => $placeName, 'adminCode1' => $adminCode, 'featureClass' => 'P'];

        $geonamesData = $this->getDataFromApi($this->getGeonamesQuery($params,'search'));

        // First time we tries to get a data by adminCode, if data was`nt found, try to find without admin code
        if ($geonamesData->totalResultsCount == 0 && $adminCode !== null) {
            unset($params['adminCode1']);
            $geonamesData = $this->getDataFromApi($this->getGeonamesQuery($params,'search'));
        }

        return $geonamesData;
    }

    /**
     * @param $params array
     * @param $searchMethod string
     * @return string
     */
    public function getGeonamesQuery($params, $searchMethod)
    {
        $query = $this->geonamesUrl . '/' . $searchMethod . 'JSON' . '?' . $this->formatQueryString($params)
            . '&username=' . $this->geonamesLogin;

        return $query;
    }

    /**
     *
     * Get all regions for a country
     *
     * @param $countryCode string
     * @return array
     */
    public function getRegions($countryCode)
    {
        $country = Country::build($countryCode);

        $regions = [];

        foreach ($country->getStates() as $state) {
            $regions[$state->getName()] = $state->getCode();
        }

        return $regions;
    }

    /**
     *
     * Get list of all cities from geonames API
     *
     * @param $countryCode
     * @param $regionName
     * @return array
     */
    public function getCities($countryCode, $regionName)
    {
        $query = $this->getGeonamesQuery([
            'country' => $countryCode,
            'q' => $regionName,
            'featureClass' => 'p'],'search');

        $geonames = $this->getDataFromApi($query);

        $cities = [];

        if ($geonames) {
            foreach ($geonames->geonames as $key => $city) {
                $cities[$city->name] = $city->geonameId;
            }
        }

        return $cities;
    }


    /**
     * Curl for getting data from api`s
     *
     * @param $url
     * @return mixed
     */
    public function getDataFromApi($url)
    {
        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $response = curl_exec($ch);

        curl_close($ch);

        return json_decode($response);
    }

    /**
     * Builds a valid query string (url and utf8 encoded) to pass to the
     * endpoint and returns it.
     *
     * @param array $params Associative array of query parameters (name=>val)
     *
     * @return string The formatted query string
     */
    protected function formatQueryString($params = array())
    {
        $qString = array();

        foreach ($params as $name => $value) {
            if (is_array($value)) {
                foreach ($value as $val) {
                    $val = $this->isUtf8($val) ? $val : utf8_encode($val);
                    $qString[] = $name . '=' . urlencode($val);
                }
            } else {
                $value = $this->isUtf8($value) ? $value : utf8_encode($value);
                $qString[] = $name . '=' . urlencode($value);
            }
        }

        return implode('&', $qString);
    }

    /**
     * Check if the given string is a UTF-8 string or an iso-8859-1 one.
     *
     * @param string $str The string to check
     *
     * @return boolean Wether the string is unicode or not
     */
    protected function isUtf8($str)
    {
        return (bool)preg_match(
            '%^(?:
                  [\x09\x0A\x0D\x20-\x7E]            # ASCII
                | [\xC2-\xDF][\x80-\xBF]             # non-overlong 2-byte
                |  \xE0[\xA0-\xBF][\x80-\xBF]        # excluding overlongs
                | [\xE1-\xEC\xEE\xEF][\x80-\xBF]{2}  # straight 3-byte
                |  \xED[\x80-\x9F][\x80-\xBF]        # excluding surrogates
                |  \xF0[\x90-\xBF][\x80-\xBF]{2}     # planes 1-3
                | [\xF1-\xF3][\x80-\xBF]{3}          # planes 4-15
                |  \xF4[\x80-\x8F][\x80-\xBF]{2}     # plane 16
            )*$%xs',
            $str
        );
    }
}