<?php

namespace App\Form\Building;

use App\Entity\Building\ModuleAccess;
use App\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\Type\DateType;

class AccessType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('expiredAt', DateType::class, [
                'widget'  => 'single_text',
                'format' => $options['date_format'],
                'html5'   => false,
                'attr' => [
                    'data-type' => 'date',
                    'class' => 'datepicker form-control'
                ],
                'required' => false
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ModuleAccess::class,
            'date_format' => User::DATE_FORMATS[1]
        ]);
    }

    public function getName()
    {
        return 'access';
    }

}
