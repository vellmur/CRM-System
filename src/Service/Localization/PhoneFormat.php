<?php

namespace App\Service\Localization;

use App\Service\Data\CountryInfo;

/**
 * Class PhoneFormat
 * @package App\Service\Localization
 */
class PhoneFormat
{
    private $phoneCode;

    private $phoneFormat;

    /**
     * PhoneFormat constructor.
     * @param string $countryCode
     */
    public function __construct(string $countryCode)
    {
        $countryInfo = CountryInfo::getCountryInfo($countryCode);

        $this->phoneCode = $countryInfo['country_code'];
        $this->phoneFormat = $countryInfo['phone_format'];
    }

    /**
     * @return string
     */
    public function getPhonePrefix(): string
    {
        return '+' . $this->phoneCode;
    }

    /**
     * @return string
     */
    public function getPhoneFormat(): string
    {
        return $this->phoneFormat;
    }

    /**
     * @return int
     */
    public function getDigitsNum(): int
    {
        return strlen(str_replace(' ', '', $this->phoneCode . $this->phoneFormat));
    }

    /**
     * @return string
     */
    public function getMask(): string
    {
        return '+' . $this->phoneCode . ' ' . $this->phoneFormat;
    }

    /**
     * @return int
     */
    public function getMaskLength(): int
    {
        return strlen($this->getMask());
    }
}