<?php

namespace App\Form\Building;

use App\Entity\Building\Affiliate;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class AffiliateType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => false,
                'label' => 'Name',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'data-type' => 'string',
                    'data-empty' => 'false',
                    'class' => 'form-control'
                ]
            ])
            ->add('email', TextType::class, [
                'required' => false,
                'label' => 'Email',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'data-empty' => 'false',
                    'class' => 'form-control'
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
            'data_class' => Affiliate::class
        ]);
    }


}
