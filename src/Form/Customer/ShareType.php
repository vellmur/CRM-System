<?php

namespace App\Form\Customer;

use App\Entity\Customer\Share;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class ShareType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'customer.level.name',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'data-type' => 'string',
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.level.name'
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'customer.level.description',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'data-empty' => 'false',
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.level.description'
                ]
            ])
            ->add('price', TextType::class, [
                'required' => false,
                'label' => 'customer.level.price',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label    '
                ],
                'attr' => [
                    'data-type' => 'number',
                    'data-empty' => 'false',
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.level.price'
                ]
            ])
            ->add('isActive', CheckboxType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'switchery'
                ]
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Share::class,
            'translation_domain' => 'labels'
        ]);
    }
}
