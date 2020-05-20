<?php

namespace App\Form\Building;

use App\Entity\Building\Address;
use App\Form\Subscriber\TimezoneSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotNull;

class AddressType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('country', ChoiceType::class, [
                'required' => false,
                'choices' => [
                    'Россия' => 'ru',
                    'Україна' => 'ua'
                ],
                'attr' => [
                    'class' => 'select'
                ]
            ])
            ->add('street', TextType::class, [
                'attr' => [
                    'class' => 'form-control text-uppercase readonly-visible',
                    'placeholder' => 'owner.address.street',
                    'data-type' => 'string',
                    'onfocus'=> "this.removeAttribute('readonly');",
                    'readonly' => 'readonly'
                ],
                'constraints' => [
                    new NotNull()
                ]
            ])
            ->add('postalCode', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('region', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
            ->add('city', TextType::class, [
                'attr' => [
                    'class' => 'form-control'
                ],
                'required' => false
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
            'translation_domain' => 'labels'
        ]);
    }
}
