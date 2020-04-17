<?php

namespace App\Service;

class CountryList
{
    private $locales = [
        'ab' => 'Abkhazian',
        'aa' => 'Afar',
        'af' => 'Afrikaans',
        'sq' => 'Albanian',
        'am' => 'Amharic',
        'ar' => 'Arabic',
        'an' => 'Aragonese',
        'hy' => 'Armenian',
        'as' => 'Assamese',
        'ast' => 'Asturian',
        'ay' => 'Aymara',
        'az' => 'Azerbaijani',
        'ba' => 'Bashkir',
        'eu' => 'Basque',
        'bn' => 'Bengali/Bangla',
        'ber' => 'Berber',
        'dz' => 'Bhutani',
        'bh' => 'Bihari',
        'byn' => 'Bilin',
        'bi' => 'Bislama',
        'bs' => 'Bosnian',
        'br' => 'Breton',
        'bg' => 'Bulgarian',
        'my' => 'Burmese',
        'be' => 'Byelorussian',
        'km' => 'Cambodian',
        'ca' => 'Catalan',
        'hne' => 'Chhattisgarhi',
        'zh' => 'Chinese',
        'kw' => 'Cornish',
        'co' => 'Corsican',
        'crh' => 'Crimean Tatar',
        'hr' => 'Croatian',
        'cs' => 'Czech',
        'da' => 'Danish',
        'dv' => 'Dhivehi',
        'nl' => 'Dutch',
        'en' => 'English',
        'eo' => 'Esperanto',
        'et' => 'Estonian',
        'fo' => 'Faeroese',
        'fj' => 'Fiji',
        'fil' => 'Filipino',
        'fi' => 'Finnish',
        'fr' => 'French',
        'fy' => 'Frisian',
        'fur' => 'Friulian',
        'gl' => 'Galician',
        'lg' => 'Ganda',
        'gez' => 'Geez',
        'ka' => 'Georgian',
        'de' => 'German',
        'el' => 'Greek',
        'kl' => 'Greenlandic',
        'gn' => 'Guarani',
        'gu' => 'Gujarati',
        'ht' => 'Haitian',
        'ha' => 'Hausa',
        'iw' => 'Hebrew',
        'he' => 'Hebrew',
        'hi' => 'Hindi',
        'hu' => 'Hungarian',
        'is' => 'Icelandic',
        'ig' => 'Igbo',
        'in' => 'Indonesian',
        'id' => 'Indonesian',
        'ia' => 'Interlingua',
        'ie' => 'Interlingue',
        'iu' => 'Inuktitut',
        'ik' => 'Inupiak',
        'ga' => 'Irish',
        'it' => 'Italian',
        'ja' => 'Japanese',
        'jw' => 'Javanese',
        'kn' => 'Kannada',
        'ks' => 'Kashmiri',
        'csb' => 'Kashubian',
        'kk' => 'Kazakh',
        'rw' => 'Kinyarwanda',
        'ky' => 'Kirghiz',
        'rn' => 'Kirundi',
        'ko' => 'Korean',
        'ku' => 'Kurdish',
        'lo' => 'Laothian',
        'la' => 'Latin',
        'lv' => 'Latvian/Lettish',
        'li' => 'Limburgan',
        'ln' => 'Lingala',
        'lt' => 'Lithuanian',
        'nds' => 'Low German',
        'mk' => 'Macedonian',
        'mai' => 'Maithili',
        'mg' => 'Malagasy',
        'ms' => 'Malay',
        'ml' => 'Malayalam',
        'mt' => 'Maltese',
        'gv' => 'Manx',
        'mi' => 'Maori',
        'mr' => 'Marathi',
        'mo' => 'Moldavian',
        'mn' => 'Mongolian',
        'na' => 'Nauru',
        'ne' => 'Nepali',
        'se' => 'Northern Sami',
        'no' => 'Norwegian',
        'nb' => 'Norwegian Bokmål',
        'nn' => 'Norwegian Nynorsk',
        'oc' => 'Occitan',
        'or' => 'Oriya',
        'om' => 'Oromoor/Oriya',
        'os' => 'Ossetian',
        'pap' => 'Papiamento',
        'ps' => 'Pashto/Pushto',
        'nso' => 'Pedi',
        'fa' => 'Persian',
        'pl' => 'Polish',
        'pt' => 'Portuguese',
        'pa' => 'Punjabi',
        'qu' => 'Quechua',
        'rm' => 'Rhaeto-Romance',
        'ro' => 'Romanian',
        'ru' => 'Russian',
        'sm' => 'Samoan',
        'sg' => 'Sangro',
        'sa' => 'Sanskrit',
        'sc' => 'Sardinian',
        'gd' => 'Scots/Gaelic',
        'sr' => 'Serbian',
        'sh' => 'Serbo-Croatian',
        'st' => 'Sesotho',
        'tn' => 'Setswana',
        'sn' => 'Shona',
        'shs' => 'Shuswap',
        'sid' => 'Sidamo',
        'sd' => 'Sindhi',
        'si' => 'Singhalese',
        'ss' => 'Siswati',
        'sk' => 'Slovak',
        'sl' => 'Slovenian',
        'so' => 'Somali',
        'nr' => 'South Ndebele',
        'es' => 'Spanish',
        'su' => 'Sundanese',
        'sw' => 'Swahili',
        'sv' => 'Swedish',
        'tl' => 'Tagalog',
        'tg' => 'Tajik',
        'ta' => 'Tamil',
        'tt' => 'Tatar',
        'te' => 'Tegulu',
        'th' => 'Thai',
        'bo' => 'Tibetan',
        'tig' => 'Tigre',
        'ti' => 'Tigrinya',
        'to' => 'Tonga',
        'ts' => 'Tsonga',
        'tr' => 'Turkish',
        'tk' => 'Turkmen',
        'tw' => 'Twi',
        'ug' => 'Uighur',
        'uk' => 'Ukrainian',
        'hsb' => 'Upper Sorbian',
        'ur' => 'Urdu',
        'uz' => 'Uzbek',
        've' => 'Venda',
        'vi' => 'Vietnamese',
        'vo' => 'Volapuk',
        'wa' => 'Walloon',
        'cy' => 'Welsh',
        'wo' => 'Wolof',
        'xh' => 'Xhosa',
        'yi' => 'Yiddish',
        'ji' => 'Yiddish',
        'yo' => 'Yoruba',
        'zu' => 'Zulu'
    ];

    /**
     * @return array
     */
    public function getLocales() : array
    {
        return $this->locales;
    }

    /**
     * @param string $locale
     * @return mixed|null
     */
    public function getLanguageByLocale(string $locale)
    {
        return isset($this->locales[$locale]) ? $this->locales[$locale] : null;
    }

    /**
     * @param array $locales
     * @return array
     */
    public function getLanguagesByLocales(array $locales)
    {
        return array_intersect(array_flip($this->locales), $locales);
    }

    /**
     * @param $existingLanguages
     * @return array
     */
    public function getNonExistentTranslations($existingLanguages)
    {
        return array_diff(array_flip($this->locales), $existingLanguages);
    }
}
