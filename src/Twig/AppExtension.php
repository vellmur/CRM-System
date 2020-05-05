<?php

namespace App\Twig;

use App\Entity\Customer\Customer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig\Extension\AbstractExtension;

class AppExtension extends AbstractExtension
{
    private $translator;

    private $router;

    /**
     * AppExtension constructor.
     * @param TranslatorInterface $translator
     * @param UrlGeneratorInterface $router
     */
    public function __construct(TranslatorInterface $translator, UrlGeneratorInterface $router)
    {
        $this->translator = $translator;
        $this->router = $router;
    }

    /**
     * @return array
     */
    public function getFilters()
    {
        return [
            new TwigFilter('profile_link', [$this, 'getProfileLink']),
            new TwigFilter('file_size', [$this, 'formatSizeUnits'])
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
     * @param Customer $customer
     * @param string|null $locale
     * @return string|string[]
     */
    public function getProfileLink(Customer $customer, ?string $locale)
    {
        // Generate absolute url
        $link = $this->router->generate('membership_profile', [
            '_locale' => $locale ? $locale : 'en',
            'token' => $customer->getToken()
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
}