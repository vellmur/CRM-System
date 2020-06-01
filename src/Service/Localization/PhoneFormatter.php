<?php

namespace App\Service\Localization;

use App\Service\Data\CountryInfo;

/**
 * Class PhoneFormatter
 * @package App\Service\Localization
 */
class PhoneFormatter
{
    private $phoneCode;

    private $phoneFormat;

    /**
     * PhoneFormatter constructor.
     * @param string $countryCode
     * @throws \Exception
     */
    public function __construct(string $countryCode)
    {
        $countryInfo = CountryInfo::getCountryInfo($countryCode);

        $this->phoneCode = $countryInfo['country_code'];
        $this->phoneFormat = $countryInfo['phone_format'];
    }

    /**
     * @param string $phone
     * @return string
     */
    public function getCleanPhoneNumber(string $phone): string
    {
        if (strlen($phone) < $this->getMaskLength()) {
            return $phone;
        }

        // Remove all chars
        $phone = preg_replace('/[^0-9]/', '', $phone);
        $digitsNumber = $this->getDigitsNum();

        return strlen($phone) > $digitsNumber ? substr($phone, 0, $digitsNumber) : $phone;
    }

    /**
     * @param string $phone
     * @return string
     */
    public function getLocalizedPhone(string $phone): string
    {
        if (strlen($phone) !== $this->getDigitsNum()) {
            return $phone;
        }

        $phone = $this->addSpacesByFormat($this->getPhoneFormat(), $phone);

        return  '+' . $phone;
    }

    /**
     * @param string $phoneFormat
     * @param string $phone
     * @return string
     */
    private function addSpacesByFormat(string $phoneFormat, string $phone): string
    {
        $explodedFormat = str_split($phoneFormat);
        $spacesPositions = [];

        foreach ($explodedFormat as $key => $char) {
            if ($char == ' ') {
                $spacesPositions[] = $key - count($spacesPositions);
            }
        }

        $explodedPhone = str_split($phone);
        $localizedPhone = '';

        foreach ($explodedPhone as $key => $digit) {
            if (in_array($key, $spacesPositions)) {
                $localizedPhone .= ' ';
            }

            $localizedPhone .= $digit;
        }

        return $localizedPhone;
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