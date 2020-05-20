<?php

namespace App\Service\Localization;

/**
 * Class PhoneFormatter
 * @package App\Service\Localization
 */
class PhoneFormatter
{
    private $format;

    private $phone;

    /**
     * PhoneFormatter constructor.
     * @param PhoneFormat $phoneFormat
     * @param string $phone
     */
    public function __construct(PhoneFormat $phoneFormat, string $phone)
    {
        $this->format = $phoneFormat;
        $this->phone = $phone;
    }

    /**
     * Returns clear phone number without mask and spaces. Just digits.
     * @return string|null
     */
    public function getCleanPhoneNumber(): string
    {
        if (strlen($this->phone) < $this->format->getMaskLength()) {
            return $this->phone;
        }

        // Remove all chars
        $phone = preg_replace('/[^0-9]/', '', $this->phone);
        $digitsNumber = $this->format->getDigitsNum();

        return strlen($phone) > $digitsNumber ? substr($phone, 0, $digitsNumber) : $phone;
    }

    /**
     * @return string
     */
    public function getLocalizedPhone(): string
    {
        if (strlen($this->phone) !== $this->format->getDigitsNum()) {
            return $this->phone;
        }

        $phone = $this->addSpacesByFormat($this->format->getPhoneFormat(), $this->phone);

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
}