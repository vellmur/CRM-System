<?php

namespace App\Form\Customer;

use App\Entity\Customer\Product;
use App\Entity\Customer\ShareProduct;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Contracts\Translation\TranslatorInterface;

class ShareProductType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $weightLabel = $this->translator->trans('product.product_weight', [
            '%format%' => $options['client']->getWeightName()
        ], 'labels');

        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_translation_domain' => 'choices',
                'choices' => $options['products'],
                'choice_attr' => function($product) {
                    $attr = [
                        'data-weight' => $product->getWeight(),
                        'data-price' => $product->getPrice()
                    ];

                    return $attr;
                },
                'attr' => [
                    'class' => 'form-control',
                    'data-type' => 'string',
                    'data-empty' => 'false',
                ],
                'label' => 'product.product',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'required' => false,
                'placeholder' => '',
                'empty_data' => false
            ])
            ->add('qty', NumberType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'data-type' => 'number',
                    'data-empty' => 'false',
                    'min' => 1,
                    'max' => 10000,
                    'step' => 1,
                ],
                'label' => 'product.qty',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
            ])
            ->add('weight', NumberType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'data-type' => 'number',
                    'data-empty' => 'false',
                    'data-format' => $options['client']->getWeightName()
                ],
                'label' => $weightLabel,
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
            ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ShareProduct::class,
            'translation_domain' => 'labels',
            'products' => null,
            'client' => null
        ]);
    }
}