<?php

namespace App\Service\Localization;

class CurrencyFormatter
{
    const LIST = [
        2 => 'RUB',
        3 => 'UAH'
    ];

    const SYMBOL_LIST = [
        'RUB' => '&#8381;',
        'UAH' => '&#8372;'
    ];

    const CURRENCY_COUNTRY = [
        'RUB' => 'ru',
        'UAH' => 'ua'
    ];

    /**
     * @param int $id
     * @return string|null
     */
    public function getCurrencySymbolById(int $id) :? string
    {
        $currency = self::LIST[$id];

        return self::SYMBOL_LIST[$currency];
    }

    /**
     * @param string $currencyCode
     * @return string|null
     */
    public function getCurrencyCountry(string $currencyCode): ?string
    {
        $currencyCode = mb_strtoupper($currencyCode);

        if (!isset(self::CURRENCY_COUNTRY[$currencyCode])) {
            return null;
        }

        return self::CURRENCY_COUNTRY[$currencyCode];
    }
}