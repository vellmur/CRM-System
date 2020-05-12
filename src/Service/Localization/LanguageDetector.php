<?php

namespace App\Service\Localization;

class LanguageDetector
{
    private const LANGUAGES = [
        1 => [
            'language' => 'English',
            'locale_code' => 'en',
            'country_code' => 'gb'
        ],
        2 => [
            'language' => 'Русский',
            'locale_code' => 'ru',
            'country_code' => 'ru'
        ],
        3 => [
            'language' => 'Українська',
            'locale_code' => 'uk',
            'country_code' => 'ua'
        ]
    ];

    /**
     * @param int $id
     * @return string|null
     * @throws \Exception
     */
    public function getCountryCodeById(int $id): ?string
    {
        if (!isset(self::LANGUAGES[$id])) {
            throw new \Exception('Locale id was not found. Requested id is: ' . $id . '.');
        }

        return self::LANGUAGES[$id]['country_code'];
    }

    /**
     * @param int $id
     * @return string|null
     */
    public function getLocaleCodeById(int $id): ?string
    {
        if (!isset(self::LANGUAGES[$id])) {
            return null;
        }

        return self::LANGUAGES[$id]['locale_code'];
    }

    /**
     * @param string $code
     * @return mixed|null
     */
    public function getLocaleIdByCode(string $code): ?int
    {
        $locales = [];

        for ($i = 1; $i <= count(self::LANGUAGES); $i++) {
            $locales[self::LANGUAGES[$i]['locale_code']] = $i;
        }

        if (!isset($locales[$code])) {
            return null;
        }

        return $locales[$code];
    }

    /**
     * @return array
     */
    public function getLanguagesList()
    {
        $languages = [];

        for ($i = 1; $i <= count(self::LANGUAGES);$i++) {
            $languages[$i] = self::LANGUAGES[$i]['language'];
        }

        return $languages;
    }
}