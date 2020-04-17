<?php

namespace App\Form\Customer;

use App\Entity\Customer\Vendor;
use App\Entity\Customer\VendorOrder;
use Symfony\Component\Form\AbstractType;
use App\Form\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class VendorOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('vendor', EntityType::class, [
                'class' => Vendor::class,
                'label' => 'customer.orders.vendor',
                'choices' => $options['vendors'],
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'select',
                    'data-empty' => 'false',
                ],
                'required' => false,
                'placeholder' => false,
                'empty_data' => false
            ])
            ->add('orderDate', DateType::class, [
                'widget'  => 'single_text',
                'format' => $options['date_format'],
                'html5'   => false,
                'attr' => [
                    'data-type' => 'date',
                    'data-days-count' => 'true',
                    'class' => 'datepicker form-control',
                ],
                'label' => 'customer.orders.order_date',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
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
            'data_class' => VendorOrder::class,
            'translation_domain' => 'labels',
            'vendors' => null,
            'date_format' => null
        ]);
    }
}