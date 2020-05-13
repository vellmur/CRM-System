<?php

namespace App\Form\Type;

use App\Service\Localization\CurrencyFormatter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CurrencyType extends AbstractType
{
    private $currencyFormatter;

    public function __construct(CurrencyFormatter $currencyFormatter)
    {
        $this->currencyFormatter = $currencyFormatter;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $currencies = $this->currencyFormatter::SYMBOL_LIST;
        ksort($currencies);

        $resolver->setDefaults([
            'choices' => $currencies,
            'choice_attr' => function ($choice, $key, $value) {
                return [
                    'data-country-code' => $this->currencyFormatter->getCurrencyCountry($key)
                ];
            },
            'placeholder' => '',
            'label' => 'account.settings.currency',
            'attr' => [
                'class' => 'select'
            ]
        ]);
    }

    /**
     * @return string|null
     */
    public function getParent()
    {
        return ChoiceType::class;
    }
}