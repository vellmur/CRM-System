<?php

namespace App\Form\Customer;

use App\Entity\Customer\CustomerShare;
use App\Entity\Customer\Share;
use App\Form\EventListener\ShareSubscriber;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use App\Form\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SharesType extends AbstractType
{
    private $shareListener;

    public function __construct(ShareSubscriber $subscriber)
    {
        $this->shareListener = $subscriber;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('share', EntityType::class, [
                'class' => Share::class,
                'label' => 'customer.share.type',
                'choices' => $options['client']->getShares(),
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
                    'data-empty' => 'false',
                    'class' => 'datepicker form-control text-uppercase',
                    'placeholder' => 'customer.orders.start_date'
                ],
                'label' => 'customer.orders.start_date',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'required' => false
            ]);

        $builder->addEventSubscriber($this->shareListener);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CustomerShare::class,
            'translation_domain' => 'labels',
            'date_format' => null,
            'client' => null
        ]);
    }
}