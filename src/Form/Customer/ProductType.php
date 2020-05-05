<?php

namespace App\Form\Customer;

use App\Entity\Customer\Product;
use App\Form\Subscriber\ProductSubscriber;
use App\Form\Type\PriceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    private $subscriber;

    public function __construct(ProductSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isPos', CheckboxType::class, [
                'required' => false,
                'label' => 'POS',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'switchery'
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'product.name',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'product.name'
                ]
            ])
            ->add('description', TextareaType::class, [
                'required' => false,
                'label' => 'product.description',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'rows' => 3,
                    'class' => 'form-control',
                    'placeholder' => 'product.description'
                ]
            ])
            ->add('price', PriceType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control price-field',
                    'placeholder' => 'product.price'
                ],
                'label' => 'product.price',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ]
            ])
            ->add('deliveryPrice', PriceType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control price-field',
                    'placeholder' => 'product.delivery_price'
                ],
                'label' => 'product.delivery_price',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
            ])
            ->add('sku', TextType::class, [
                'required' => false,
                'label' => 'product.sku',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'data-behavior' => 'uppercase',
                    'class' => 'form-control',
                    'placeholder' => 'product.sku'
                ]
            ]);

        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'labels',
            'data_class' => Product::class,
            'client' => null,
            'isTopForm' => null
        ]);
    }
}