<?php

namespace App\Form\Customer;

use App\Entity\Customer\POSProduct;
use App\Entity\Customer\Product;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class POSProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'attr' => [
                    'class' => 'hidden customer-product'
                ],
                'label' => false,
                'required' => false
            ])
            ->add('qty', NumberType::class, [
                'attr' => [
                    'class' => 'product-qty hidden',
                    'data-type' => 'string',
                    'data-empty' => 'false',
                ],
                'label' => false,
                'required' => false,
            ])
            ->add('weight', NumberType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control product-weight hidden',
                    'data-type' => 'number',
                    'data-empty' => 'false',
                ],
                'label' => 'Weight',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ]
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'labels',
            'data_class' => POSProduct::class
        ]);
    }
}