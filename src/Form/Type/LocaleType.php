<?php

namespace App\Form\Type;

use App\Service\Localization\LanguageDetector;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\RouterInterface;

class LocaleType extends AbstractType
{
    private $router;

    private $request;

    /**
     * LocaleType constructor.
     * @param RouterInterface $router
     */
    public function __construct(RouterInterface $router, RequestStack $requestStack)
    {
        $this->router = $router;
        $this->request = $requestStack->getCurrentRequest();
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $languageDetector = new LanguageDetector();
        $locales = array_flip($languageDetector->getLanguagesList());
        $currentLocale = $this->request ? $languageDetector->getLocaleIdByCode($this->request->getLocale()) : null;

        $resolver->setDefaults([
            'choices' => $locales,
            'label' => 'account.settings.language',
            'attr' => [
                'class' => 'select',
                'data-element' => 'locale'
            ],
            'choice_attr' => function ($choice, $key, $value) use ($languageDetector) {
                return [
                    'data-content' => '<span class="flag-icon flag-icon-' . $languageDetector->getCountryCodeById($value) . '"></span> ' . $key,
                    'data-switch-path' => $this->request ? $this->getSwitchPath($languageDetector, $value) : ''
                ];
            },
            'data' => $currentLocale ?? 1,
            'placeholder' => false
        ]);
    }

    private function getSwitchPath(LanguageDetector $languageDetector, $localeId)
    {
        $localeCode = $languageDetector->getLocaleCodeById($localeId);
        $params = array_merge($this->request->get('_route_params'), ['_locale' => $localeCode]);
        $switchPath = $this->router->generate($this->request->get('_route'), $params);

        return $switchPath;
    }

    /**
     * @return string|null
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}