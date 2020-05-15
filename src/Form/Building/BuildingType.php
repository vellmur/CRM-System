<?php

namespace App\Form\Building;

use App\Entity\Building\Building;
use App\Form\Subscriber\TimezoneSubscriber;
use App\Form\Type\CurrencyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class BuildingType extends AbstractType
{
    public $timezoneSubscriber;

    /**
     * BuildingType constructor.
     * @param TimezoneSubscriber $timezoneSubscriber
     */
    public function __construct(TimezoneSubscriber $timezoneSubscriber)
    {
        $this->timezoneSubscriber = $timezoneSubscriber;
    }
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'translation_domain' => 'messages',
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'register.company_name'
                ]
            ])
            ->add('email', TextType::class, [
                'required' => false,
                'label' => 'customer.add.email',
                'label_attr' => [
                    'class' => 'control-label'
                ],
                'attr' => [
                    'data-empty' => 'false',
                    'class' => 'form-control'
                ]
            ])
            ->add('currency', CurrencyType::class, [
                'required' => false
            ])
            ->add('address', AddressType::class)
        ;

        $builder->addEventSubscriber($this->timezoneSubscriber);
    }


    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Building::class,
            'validation_groups' => ['register_validation', 'profile_validation'],
            'translation_domain' => 'labels'
        ]);
    }
}
