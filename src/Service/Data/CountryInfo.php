<?php

namespace App\Service\Data;

class CountryInfo
{
    /**
     * @param string $countryCode
     * @return mixed
     */
    public static function getCountryInfo(string $countryCode)
    {
        $countryCode = mb_strtoupper($countryCode);

        return self::$countryInfo[$countryCode];
    }

    /**
     * @var array
     */
    private static $countryInfo = [
        'AF' => [
            'territoryType' => '',
            'country_code' => '93',
            'phone_format' => '99 999 9999'
        ],
        'AX' => [
            'territoryType' => '',
            'country_code' => '213',
            'phone_format' => '999 99 99 99',
        ],
        'AL' => [
            'territoryType' => '',
            'country_code' => '355',
            'phone_format' => '99 999 9999',
        ],
        'DZ' => [
            'territoryType' => '',
            'country_code' => '213',
            'phone_format' => '999 99 99 99',
        ],
        'AS' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'AD' => [
            'territoryType' => '',
            'country_code' => '376',
            'phone_format' => '999 999',
        ],
        'AO' => [
            'territoryType' => '',
            'country_code' => '244',
            'phone_format' => '999 999 999',
        ],
        'AI' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'AQ' => [
            'territoryType' => '',
            'country_code' => '672',
            'phone_format' => '99 9999',
        ],
        'AG' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'AR' => [
            'territoryType' => '',
            'country_code' => '54',
            'phone_format' => '99 99 9999 9999',
        ],
        'AM' => [
            'territoryType' => '',
            'country_code' => '374',
            'phone_format' => '99 999999',
        ],
        'AW' => [
            'territoryType' => '',
            'country_code' => '297',
            'phone_format' => '999 9999',
        ],
        'AU' => [
            'territoryType' => 'State/Territory',
            'country_code' => '61',
            'phone_format' => '999 999 999',
        ],
        'AT' => [
            'territoryType' => '',
            'country_code' => '43',
            'phone_format' => '999 999999',
        ],
        'AZ' => [
            'territoryType' => '',
            'country_code' => '994',
            'phone_format' => '99 999 99 99',
        ],
        'BS' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'BH' => [
            'territoryType' => '',
            'country_code' => '973',
            'phone_format' => '9999 9999',
        ],
        'BD' => [
            'territoryType' => '',
            'country_code' => '880',
            'phone_format' => '9999 999999',
        ],
        'BB' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'BY' => [
            'territoryType' => '',
            'country_code' => '375',
            'phone_format' => '99 999 9999',
        ],
        'BE' => [
            'territoryType' => '',
            'country_code' => '32',
            'phone_format' => '999 99 99 99',
        ],
        'BZ' => [
            'territoryType' => '',
            'country_code' => '501',
            'phone_format' => '999 9999',
        ],
        'BJ' => [
            'territoryType' => '',
            'country_code' => '229',
            'phone_format' => '99 99 99 99',
        ],
        'BM' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'BT' => [
            'territoryType' => '',
            'country_code' => '975',
            'phone_format' => '99 99 99 99',
        ],
        'BO' => [
            'territoryType' => '',
            'country_code' => '591',
            'phone_format' => '99999999',
        ],
        'BQ' => [
            'territoryType' => '',
            'country_code' => '599',
            'phone_format' => '999 9999',
        ],
        'BA' => [
            'territoryType' => '',
            'country_code' => '387',
            'phone_format' => '99 999 999',
        ],
        'BW' => [
            'territoryType' => '',
            'country_code' => '267',
            'phone_format' => '99 999 999',
        ],
        'BV' => [
            'territoryType' => '',
            'country_code' => '55',
            'phone_format' => '99 9999',
        ],
        'BR' => [
            'territoryType' => '',
            'country_code' => '55',
            'phone_format' => '99 9999 9999',
        ],
        'IO' => [
            'territoryType' => '',
            'country_code' => '246',
            'phone_format' => '999 9999',
        ],
        'BN' => [
            'territoryType' => '',
            'country_code' => '673',
            'phone_format' => '999 9999',
        ],
        'BG' => [
            'territoryType' => '',
            'country_code' => '359',
            'phone_format' => '99 999 999',
        ],
        'BF' => [
            'territoryType' => '',
            'country_code' => '226',
            'phone_format' => '99 99 99 99',
        ],
        'BI' => [
            'territoryType' => '',
            'country_code' => '257',
            'phone_format' => '99 99 99 99',
        ],
        'CV' => [
            'territoryType' => '',
            'country_code' => '238',
            'phone_format' => '999 99 99',
        ],
        'KH' => [
            'territoryType' => '',
            'country_code' => '855',
            'phone_format' => '99 999 999',
        ],
        'CM' => [
            'territoryType' => '',
            'country_code' => '237',
            'phone_format' => '99 99 99 99',
        ],
        'CA' => [
            'territoryType' => 'Province',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'KY' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'CF' => [
            'territoryType' => '',
            'country_code' => '236',
            'phone_format' => '99 99 99 99',
        ],
        'TD' => [
            'territoryType' => '',
            'country_code' => '235',
            'phone_format' => '99 99 99 99',
        ],
        'CL' => [
            'territoryType' => '',
            'country_code' => '56',
            'phone_format' => '9 9999 9999',
        ],
        'CN' => [
            'territoryType' => '',
            'country_code' => '86',
            'phone_format' => '999 9999 9999',
        ],
        'CX' => [
            'territoryType' => '',
            'country_code' => '61',
            'phone_format' => '999 999 999',
        ],
        'CC' => [
            'territoryType' => '',
            'country_code' => '61',
            'phone_format' => '999 999 999',
        ],
        'CO' => [
            'territoryType' => '',
            'country_code' => '57',
            'phone_format' => '999 9999999',
        ],
        'KM' => [
            'territoryType' => '',
            'country_code' => '269',
            'phone_format' => '999 99 99',
        ],
        'CG' => [
            'territoryType' => '',
            'country_code' => '243',
            'phone_format' => '999 999 999',
        ],
        'CD' => [
            'territoryType' => '',
            'country_code' => '242',
            'phone_format' => '9 999 9999',
        ],
        'CK' => [
            'territoryType' => '',
            'country_code' => '682',
            'phone_format' => '99 999',
        ],
        'CR' => [
            'territoryType' => '',
            'country_code' => '506',
            'phone_format' => '9999 9999',
        ],
        'CI' => [
            'territoryType' => '',
            'country_code' => '225',
            'phone_format' => '99 99 99 99',
        ],
        'HR' => [
            'territoryType' => '',
            'country_code' => '385',
            'phone_format' => '99 999 9999',
        ],
        'CU' => [
            'territoryType' => '',
            'country_code' => '53',
            'phone_format' => '9 9999999',
        ],
        'CW' => [
            'territoryType' => '',
            'country_code' => '599',
            'phone_format' => '9 999 9999',
        ],
        'CY' => [
            'territoryType' => '',
            'country_code' => '357',
            'phone_format' => '99 999999',
        ],
        'CZ' => [
            'territoryType' => '',
            'country_code' => '420',
            'phone_format' => '999 999 999',
        ],
        'DK' => [
            'territoryType' => '',
            'country_code' => '45',
            'phone_format' => '99 99 99 99',
        ],
        'DJ' => [
            'territoryType' => '',
            'country_code' => '253',
            'phone_format' => '99 99 99 99',
        ],
        'DM' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'DO' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'EC' => [
            'territoryType' => '',
            'country_code' => '593',
            'phone_format' => '99 999 9999',
        ],
        'EG' => [
            'territoryType' => '',
            'country_code' => '20',
            'phone_format' => '999 999 9999',
        ],
        'SV' => [
            'territoryType' => '',
            'country_code' => '503',
            'phone_format' => '9999 9999',
        ],
        'GQ' => [
            'territoryType' => '',
            'country_code' => '240',
            'phone_format' => '999 999 999',
        ],
        'ER' => [
            'territoryType' => '',
            'country_code' => '291',
            'phone_format' => '9 999 999',
        ],
        'EE' => [
            'territoryType' => '',
            'country_code' => '372',
            'phone_format' => '9999 9999',
        ],
        'ET' => [
            'territoryType' => '',
            'country_code' => '251',
            'phone_format' => '99 999 9999',
        ],
        'FK' => [
            'territoryType' => '',
            'country_code' => '500',
            'phone_format' => '99999',
        ],
        'FO' => [
            'territoryType' => '',
            'country_code' => '298',
            'phone_format' => '999999',
        ],
        'FJ' => [
            'territoryType' => '',
            'country_code' => '679',
            'phone_format' => '999 9999',
        ],
        'FI' => [
            'territoryType' => '',
            'country_code' => '358',
            'phone_format' => '99 9999999',
        ],
        'FR' => [
            'territoryType' => '',
            'country_code' => '33',
            'phone_format' => '9 99 99 99 99',
        ],
        'GF' => [
            'territoryType' => '',
            'country_code' => '594',
            'phone_format' => '999 99 99 99',
        ],
        'PF' => [
            'territoryType' => '',
            'country_code' => '689',
            'phone_format' => '99 99 99',
        ],
        'TF' => [
            'territoryType' => '',
            'country_code' => '596',
            'phone_format' => '999 999 999',
        ],
        'GA' => [
            'territoryType' => '',
            'country_code' => '241',
            'phone_format' => '99 99 99 99',
        ],
        'GM' => [
            'territoryType' => '',
            'country_code' => '220',
            'phone_format' => '999 9999',
        ],
        'GE' => [
            'territoryType' => '',
            'country_code' => '995',
            'phone_format' => '999 99 99 99',
        ],
        'DE' => [
            'territoryType' => '',
            'country_code' => '49',
            'phone_format' => '999 99999999',
        ],
        'GH' => [
            'territoryType' => '',
            'country_code' => '233',
            'phone_format' => '99 999 9999',
        ],
        'GI' => [
            'territoryType' => '',
            'country_code' => '350',
            'phone_format' => '99999999',
        ],
        'GR' => [
            'territoryType' => '',
            'country_code' => '30',
            'phone_format' => '999 999 9999',
        ],
        'GL' => [
            'territoryType' => '',
            'country_code' => '299',
            'phone_format' => '99 99 99',
        ],
        'GD' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'GP' => [
            'territoryType' => '',
            'country_code' => '590',
            'phone_format' => '999 99 9999',
        ],
        'GU' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'GT' => [
            'territoryType' => '',
            'country_code' => '502',
            'phone_format' => '9999 9999',
        ],
        'GG' => [
            'territoryType' => '',
            'country_code' => '44',
            'phone_format' => '9999 999999',
        ],
        'GN' => [
            'territoryType' => '',
            'country_code' => '224',
            'phone_format' => '99 99 99 99',
        ],
        'GW' => [
            'territoryType' => '',
            'country_code' => '245',
            'phone_format' => '999 9999',
        ],
        'GY' => [
            'territoryType' => '',
            'country_code' => '592',
            'phone_format' => '999 9999',
        ],
        'HT' => [
            'territoryType' => '',
            'country_code' => '509',
            'phone_format' => '99 99 9999',
        ],
        'HM' => [
            'territoryType' => '',
            'country_code' => '672',
            'phone_format' => '999 999 9999',
        ],
        'VA' => [
            'territoryType' => '',
            'country_code' => '379',
            'phone_format' => '39 06 99999999',
        ],
        'HN' => [
            'territoryType' => '',
            'country_code' => '504',
            'phone_format' => '9999 9999',
        ],
        'HK' => [
            'territoryType' => '',
            'country_code' => '852',
            'phone_format' => '9999 9999',
        ],
        'HU' => [
            'territoryType' => '',
            'country_code' => '36',
            'phone_format' => '99 999 9999',
        ],
        'IS' => [
            'territoryType' => '',
            'country_code' => '354',
            'phone_format' => '999 9999',
        ],
        'IN' => [
            'territoryType' => '',
            'country_code' => '91',
            'phone_format' => '99 99 999999',
        ],
        'ID' => [
            'territoryType' => '',
            'country_code' => '62',
            'phone_format' => '999 999 999',
        ],
        'IR' => [
            'territoryType' => '',
            'country_code' => '98',
            'phone_format' => '999 999 9999',
        ],
        'IQ' => [
            'territoryType' => '',
            'country_code' => '964',
            'phone_format' => '999 999 9999',
        ],
        'IE' => [
            'territoryType' => 'County',
            'country_code' => '353',
            'phone_format' => '99 999 9999',
        ],
        'IM' => [
            'territoryType' => '',
            'country_code' => '44',
            'phone_format' => '9999 999999',
        ],
        'IL' => [
            'territoryType' => '',
            'country_code' => '972',
            'phone_format' => '99 999 9999',
        ],
        'IT' => [
            'territoryType' => 'Province',
            'country_code' => '39',
            'phone_format' => '999 999 9999',
        ],
        'JM' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'JP' => [
            'territoryType' => 'Prefecture',
            'country_code' => '81',
            'phone_format' => '9999 9999',
        ],
        'JE' => [
            'territoryType' => '',
            'country_code' => '44',
            'phone_format' => '9999 999999',
        ],
        'JO' => [
            'territoryType' => '',
            'country_code' => '962',
            'phone_format' => '9 9999 9999',
        ],
        'KZ' => [
            'territoryType' => '',
            'country_code' => '7',
            'phone_format' => '999 999 9999',
        ],
        'KE' => [
            'territoryType' => '',
            'country_code' => '254',
            'phone_format' => '999 999999',
        ],
        'KI' => [
            'territoryType' => '',
            'country_code' => '686',
            'phone_format' => '99999',
        ],
        'KP' => [
            'territoryType' => '',
            'country_code' => '850',
            'phone_format' => '999 999 9999',
        ],
        'KR' => [
            'territoryType' => '',
            'country_code' => '82',
            'phone_format' => '999 999 9999',
        ],
        'KW' => [
            'territoryType' => '',
            'country_code' => '965',
            'phone_format' => '999 99999',
        ],
        'KG' => [
            'territoryType' => '',
            'country_code' => '996',
            'phone_format' => '999 999 999',
        ],
        'LA' => [
            'territoryType' => '',
            'country_code' => '856',
            'phone_format' => '99 99 999 999',
        ],
        'LV' => [
            'territoryType' => '',
            'country_code' => '371',
            'phone_format' => '99 999 999',
        ],
        'LB' => [
            'territoryType' => '',
            'country_code' => '961',
            'phone_format' => '99 999 999',
        ],
        'LS' => [
            'territoryType' => '',
            'country_code' => '266',
            'phone_format' => '9999 9999',
        ],
        'LR' => [
            'territoryType' => '',
            'country_code' => '231',
            'phone_format' => '9 999 999',
        ],
        'LY' => [
            'territoryType' => '',
            'country_code' => '218',
            'phone_format' => '99 9999999',
        ],
        'LI' => [
            'territoryType' => '',
            'country_code' => '423',
            'phone_format' => '999 999 999',
        ],
        'LT' => [
            'territoryType' => '',
            'country_code' => '370',
            'phone_format' => '999 99999',
        ],
        'LU' => [
            'territoryType' => '',
            'country_code' => '352',
            'phone_format' => '999 999 999',
        ],
        'MO' => [
            'territoryType' => '',
            'country_code' => '853',
            'phone_format' => '9999 9999',
        ],
        'MK' => [
            'territoryType' => '',
            'country_code' => '389',
            'phone_format' => '99 999 999',
        ],
        'MG' => [
            'territoryType' => '',
            'country_code' => '261',
            'phone_format' => '99 99 999 99',
        ],
        'MW' => [
            'territoryType' => '',
            'country_code' => '265',
            'phone_format' => '999 99 99 99',
        ],
        'MY' => [
            'territoryType' => 'State/Territory',
            'country_code' => '60',
            'phone_format' => '99 999 9999',
        ],
        'MV' => [
            'territoryType' => '',
            'country_code' => '960',
            'phone_format' => '999 9999',
        ],
        'ML' => [
            'territoryType' => '',
            'country_code' => '223',
            'phone_format' => '99 99 99 99',
        ],
        'MT' => [
            'territoryType' => '',
            'country_code' => '356',
            'phone_format' => '9999 9999',
        ],
        'MH' => [
            'territoryType' => '',
            'country_code' => '692',
            'phone_format' => '999 9999',
        ],
        'MQ' => [
            'territoryType' => '',
            'country_code' => '596',
            'phone_format' => '999 99 99 99',
        ],
        'MR' => [
            'territoryType' => '',
            'country_code' => '222',
            'phone_format' => '99 99 99 99',
        ],
        'MU' => [
            'territoryType' => '',
            'country_code' => '230',
            'phone_format' => '999 9999',
        ],
        'YT' => [
            'territoryType' => '',
            'country_code' => '262',
            'phone_format' => '999 99 99 99',
        ],
        'MX' => [
            'territoryType' => 'State',
            'country_code' => '52',
            'phone_format' => '9 999 999 9999',
        ],
        'FM' => [
            'territoryType' => '',
            'country_code' => '691',
            'phone_format' => '999 9999',
        ],
        'MD' => [
            'territoryType' => '',
            'country_code' => '373',
            'phone_format' => '999 99 999',
        ],
        'MC' => [
            'territoryType' => '',
            'country_code' => '377',
            'phone_format' => '9 99 99 99 99',
        ],
        'MN' => [
            'territoryType' => '',
            'country_code' => '976',
            'phone_format' => '9999 9999',
        ],
        'ME' => [
            'territoryType' => '',
            'country_code' => '382',
            'phone_format' => '99 999 999',
        ],
        'MS' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'MA' => [
            'territoryType' => '',
            'country_code' => '212',
            'phone_format' => '999 999999',
        ],
        'MZ' => [
            'territoryType' => '',
            'country_code' => '258',
            'phone_format' => '99 999 9999',
        ],
        'MM' => [
            'territoryType' => '',
            'country_code' => '95',
            'phone_format' => '9 999 9999',
        ],
        'NA' => [
            'territoryType' => '',
            'country_code' => '264',
            'phone_format' => '99 999 9999',
        ],
        'NR' => [
            'territoryType' => '',
            'country_code' => '674',
            'phone_format' => '999 9999',
        ],
        'NP' => [
            'territoryType' => '',
            'country_code' => '977',
            'phone_format' => '999 9999999',
        ],
        'NL' => [
            'territoryType' => '',
            'country_code' => '31',
            'phone_format' => '9 99999999',
        ],
        'NC' => [
            'territoryType' => '',
            'country_code' => '687',
            'phone_format' => '99 99 99',
        ],
        'NZ' => [
            'territoryType' => 'Region',
            'country_code' => '64',
            'phone_format' => '99 999 9999',
        ],
        'NI' => [
            'territoryType' => '',
            'country_code' => '505',
            'phone_format' => '9999 9999',
        ],
        'NE' => [
            'territoryType' => '',
            'country_code' => '227',
            'phone_format' => '99 99 99 99',
        ],
        'NG' => [
            'territoryType' => '',
            'country_code' => '234',
            'phone_format' => '999 999 9999',
        ],
        'NU' => [
            'territoryType' => '',
            'country_code' => '683',
            'phone_format' => '9999',
        ],
        'NF' => [
            'territoryType' => '',
            'country_code' => '672',
            'phone_format' => '9 99999',
        ],
        'MP' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'NO' => [
            'territoryType' => '',
            'country_code' => '47',
            'phone_format' => '999 99 999',
        ],
        'OM' => [
            'territoryType' => '',
            'country_code' => '986',
            'phone_format' => '9999 9999',
        ],
        'PK' => [
            'territoryType' => '',
            'country_code' => '92',
            'phone_format' => '999 9999999',
        ],
        'PW' => [
            'territoryType' => '',
            'country_code' => '680',
            'phone_format' => '999 9999',
        ],
        'PS' => [
            'territoryType' => '',
            'country_code' => '970',
            'phone_format' => '999 999 999',
        ],
        'PA' => [
            'territoryType' => '',
            'country_code' => '507',
            'phone_format' => '9999 9999',
        ],
        'PG' => [
            'territoryType' => '',
            'country_code' => '675',
            'phone_format' => '999 9999',
        ],
        'PY' => [
            'territoryType' => '',
            'country_code' => '595',
            'phone_format' => '999 999999',
        ],
        'PE' => [
            'territoryType' => '',
            'country_code' => '51',
            'phone_format' => '999 999 999',
        ],
        'PH' => [
            'territoryType' => '',
            'country_code' => '63',
            'phone_format' => '999 999 9999',
        ],
        'PN' => [
            'territoryType' => '',
            'country_code' => '872',
            'phone_format' => '999 999 9999',
        ],
        'PL' => [
            'territoryType' => '',
            'country_code' => '48',
            'phone_format' => '999 999 999',
        ],
        'PT' => [
            'territoryType' => '',
            'country_code' => '351',
            'phone_format' => '999 999 999',
        ],
        'PR' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'QA' => [
            'territoryType' => '',
            'country_code' => '974',
            'phone_format' => '9999 9999',
        ],
        'RE' => [
            'territoryType' => '',
            'country_code' => '262',
            'phone_format' => '999 99 99 99',
        ],
        'RO' => [
            'territoryType' => '',
            'country_code' => '40',
            'phone_format' => '999 999 999',
        ],
        'RU' => [
            'territoryType' => '',
            'country_code' => '7',
            'phone_format' => '999 999 99 99',
        ],
        'RW' => [
            'territoryType' => '',
            'country_code' => '250',
            'phone_format' => '999 999 999',
        ],
        'BL' => [
            'territoryType' => '',
            'country_code' => '590',
            'phone_format' => '999 99 9999',
        ],
        'SH' => [
            'territoryType' => '',
            'country_code' => '290',
            'phone_format' => '999 999 9999',
        ],
        'KN' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'LC' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'MF' => [
            'territoryType' => '',
            'country_code' => '590',
            'phone_format' => '999 99 9999',
        ],
        'PM' => [
            'territoryType' => '',
            'country_code' => '508',
            'phone_format' => '99 99 99',
        ],
        'VC' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'WS' => [
            'territoryType' => '',
            'country_code' => '685',
            'phone_format' => '999999',
        ],
        'SM' => [
            'territoryType' => '',
            'country_code' => '378',
            'phone_format' => '99 99 99 99',
        ],
        'ST' => [
            'territoryType' => '',
            'country_code' => '239',
            'phone_format' => '999 9999',
        ],
        'SA' => [
            'territoryType' => '',
            'country_code' => '966',
            'phone_format' => '99 999 9999',
        ],
        'SN' => [
            'territoryType' => '',
            'country_code' => '221',
            'phone_format' => '99 999 99 99',
        ],
        'RS' => [
            'territoryType' => '',
            'country_code' => '381',
            'phone_format' => '99 9999999',
        ],
        'SC' => [
            'territoryType' => '',
            'country_code' => '248',
            'phone_format' => '9 999 999',
        ],
        'SL' => [
            'territoryType' => '',
            'country_code' => '232',
            'phone_format' => '99 999999',
        ],
        'SG' => [
            'territoryType' => '',
            'country_code' => '65',
            'phone_format' => '9999 9999',
        ],
        'SX' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'SK' => [
            'territoryType' => '',
            'country_code' => '421',
            'phone_format' => '999 999 999',
        ],
        'SI' => [
            'territoryType' => '',
            'country_code' => '386',
            'phone_format' => '99 999 999',
        ],
        'SB' => [
            'territoryType' => '',
            'country_code' => '677',
            'phone_format' => '999 9999',
        ],
        'SO' => [
            'territoryType' => '',
            'country_code' => '252',
            'phone_format' => '99 999999',
        ],
        'ZA' => [
            'territoryType' => '',
            'country_code' => '27',
            'phone_format' => '99 999 9999',
        ],
        'GS' => [
            'territoryType' => '',
            'country_code' => '996',
            'phone_format' => '999 999 999',
        ],
        'SS' => [
            'territoryType' => '',
            'country_code' => '211',
            'phone_format' => '999 999 999',
        ],
        'ES' => [
            'territoryType' => 'Province',
            'country_code' => '34',
            'phone_format' => '999 99 99 99',
        ],
        'LK' => [
            'territoryType' => '',
            'country_code' => '94',
            'phone_format' => '99 999 9999',
        ],
        'SD' => [
            'territoryType' => '',
            'country_code' => '249',
            'phone_format' => '99 999 9999',
        ],
        'SR' => [
            'territoryType' => '',
            'country_code' => '597',
            'phone_format' => '999 9999',
        ],
        'SJ' => [
            'territoryType' => '',
            'country_code' => '47',
            'phone_format' => '999 99 999',
        ],
        'SZ' => [
            'territoryType' => '',
            'country_code' => '268',
            'phone_format' => '9999 9999',
        ],
        'SE' => [
            'territoryType' => '',
            'country_code' => '46',
            'phone_format' => '99 999 99 99',
        ],
        'CH' => [
            'territoryType' => '',
            'country_code' => '41',
            'phone_format' => '99 999 99 99',
        ],
        'SY' => [
            'territoryType' => '',
            'country_code' => '963',
            'phone_format' => '999 999 999',
        ],
        'TW' => [
            'territoryType' => '',
            'country_code' => '886',
            'phone_format' => '999 999 999',
        ],
        'TJ' => [
            'territoryType' => '',
            'country_code' => '992',
            'phone_format' => '999 99 9999',
        ],
        'TZ' => [
            'territoryType' => '',
            'country_code' => '255',
            'phone_format' => '999 999 999',
        ],
        'TH' => [
            'territoryType' => '',
            'country_code' => '66',
            'phone_format' => '99 999 9999',
        ],
        'TL' => [
            'territoryType' => '',
            'country_code' => '670',
            'phone_format' => '9999 9999',
        ],
        'TG' => [
            'territoryType' => '',
            'country_code' => '228',
            'phone_format' => '99 99 99 99',
        ],
        'TK' => [
            'territoryType' => '',
            'country_code' => '690',
            'phone_format' => '9999',
        ],
        'TO' => [
            'territoryType' => '',
            'country_code' => '676',
            'phone_format' => '999 9999',
        ],
        'TT' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'TN' => [
            'territoryType' => '',
            'country_code' => '216',
            'phone_format' => '99 999 999',
        ],
        'TR' => [
            'territoryType' => '',
            'country_code' => '90',
            'phone_format' => '999 999 9999',
        ],
        'TM' => [
            'territoryType' => '',
            'country_code' => '993',
            'phone_format' => '99 999999',
        ],
        'TC' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'TV' => [
            'territoryType' => '',
            'country_code' => '688',
            'phone_format' => '999999',
        ],
        'UG' => [
            'territoryType' => '',
            'country_code' => '256',
            'phone_format' => '999 999999',
        ],
        'UA' => [
            'territoryType' => '',
            'country_code' => '380',
            'phone_format' => '99 999 9999',
        ],
        'AE' => [
            'territoryType' => '',
            'country_code' => '971',
            'phone_format' => '99 999 9999',
        ],
        'GB' => [
            'territoryType' => '',
            'country_code' => '44',
            'phone_format' => '9999 999999',
        ],
        'US' => [
            'territoryType' => 'State',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'UM' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'UY' => [
            'territoryType' => '',
            'country_code' => '598',
            'phone_format' => '99 999 999',
        ],
        'UZ' => [
            'territoryType' => '',
            'country_code' => '998',
            'phone_format' => '99 999 99 99',
        ],
        'VU' => [
            'territoryType' => '',
            'country_code' => '678',
            'phone_format' => '999 9999',
        ],
        'VE' => [
            'territoryType' => '',
            'country_code' => '58',
            'phone_format' => '999 9999999',
        ],
        'VN' => [
            'territoryType' => '',
            'country_code' => '84',
            'phone_format' => '99 999 99 99',
        ],
        'VG' => [
            'territoryType' => '',
            'country_code' => '1',
            'phone_format' => '999 999 9999',
        ],
        'VI' => [
            'territoryType' => ''
        ],
        'WF' => [
            'territoryType' => '',
            'country_code' => '681',
            'phone_format' => '99 99 99',
        ],
        'EH' => [
            'territoryType' => '',
            'country_code' => '212',
            'phone_format' => '99 999999',
        ],
        'YE' => [
            'territoryType' => '',
            'country_code' => '967',
            'phone_format' => '999 999 999',
        ],
        'ZM' => [
            'territoryType' => '',
            'country_code' => '260',
            'phone_format' => '99 9999999',
        ],
        'ZW' => [
            'territoryType' => '',
            'country_code' => '263',
            'phone_format' => '99 999 9999',
        ],
    ];
}
