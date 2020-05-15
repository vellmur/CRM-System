<?php

namespace App\Form\Customer;

use App\Entity\Customer\CustomerOrders;
use App\Entity\Customer\Share;
use Symfony\Component\Form\AbstractType;
use App\Form\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CustomerOrdersType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('share', EntityType::class, [
                'class' => Share::class,
                'label' => 'customer.share.share',
                'choices' => $builder->getData()->getBuilding()->getShares(),
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
            ->add('startDate', DateType::class, [
                'widget'  => 'single_text',
                'format' => $options['date_format'],
                'html5'   => false,
                'attr' => [
                    'data-type' => 'date',
                    'data-days-count' => 'true',
                    'class' => 'datepicker form-control',
                ],
                'label' => 'customer.orders.start_date',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'required' => false
            ])
            ->add('endDate', DateType::class, [
                'widget'  => 'single_text',
                'format' => $options['date_format'],
                'html5'   => false,
                'attr' => [
                    'data-type' => 'date',
                    'data-days-count' => 'true',
                    'class' => 'datepicker form-control',
                ],
                'label' => 'customer.orders.end_date',
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
            'translation_domain' => 'labels',
            'date_format' => null,
            'data_class' => CustomerOrders::class
        ]);
    }
}