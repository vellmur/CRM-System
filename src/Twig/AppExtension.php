<?php

namespace App\Twig;

use App\Entity\Owner\Owner;
use App\Service\Localization\CurrencyFormatter;
use App\Service\Localization\PhoneFormatter;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class AppExtension extends AbstractExtension
{
    private $translator;

    private $router;

    private $currencyFormatter;

    /**
     * AppExtension constructor.
     * @param TranslatorInterface $translator
     * @param UrlGeneratorInterface $router
     * @param CurrencyFormatter $currencyFormatter
     */
    public function __construct(TranslatorInterface $translator, UrlGeneratorInterface $router, CurrencyFormatter $currencyFormatter) {
        $this->translator = $translator;
        $this->router = $router;
        $this->currencyFormatter = $currencyFormatter;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('profile_link', [$this, 'getProfileLink']),
            new TwigFilter('file_size', [$this, 'formatSizeUnits']),
            new TwigFilter('currencyFormat', [$this, 'currencyFormat']),
            new TwigFilter('formatPhoneNumber', [$this, 'formatPhoneNumber'])
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
     * @param Owner $owner
     * @param string|null $locale
     * @return string|string[]
     */
    public function getProfileLink(Owner $owner, ?string $locale = null)
    {
        // Generate absolute url
        $link = $this->router->generate('membership_profile', [
            '_locale' => $locale ? $locale : 'en',
            'token' => $owner->getToken()
        ], UrlGeneratorInterface::ABSOLUTE_URL);

        // If server is not localhost, change scheme to https
        if (!strstr($link, '127.0.0.1') && !strstr($link, 'blackdirt.local')) {
            $link = str_replace('http:', 'https:', $link);
        }

        return $link;
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

    /**
     * @param int $id
     * @return string|null
     */
    public function currencyFormat(?int $id)
    {
        return $id === null ? '$' : $this->currencyFormatter->getCurrencySymbolById($id);
    }

    /**
     * @param string $phone
     * @param string $countryCode
     * @return string|null
     * @throws \Exception
     */
    public function formatPhoneNumber(string $phone, string $countryCode)
    {
        $phoneFormatter = new PhoneFormatter($countryCode);

        return $phoneFormatter->getLocalizedPhone($phone);
    }
}