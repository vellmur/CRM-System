<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateFormatType extends AbstractType
{
    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'choices' => [
                'DD-MM-YYYY' => 1,
                'MM-DD-YYYY' => 2,
                'YYYY-MM-DD' => 3,
                'DD-MMM-YYYY' => 4
            ],
            'label' => 'account.settings.date_format',
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