<?php

namespace App\Form\Customer;

use App\Entity\Customer\Customer;
use App\Form\Type\PhoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'customer.add.firstname',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.add.firstname'
                ],

            ])
            ->add('lastname', TextType::class, [
                'label' => 'customer.add.lastname',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.add.lastname'
                ]
            ])
            ->add('phone', PhoneType::class, [
                'label' => 'customer.add.phone',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'customer.add.phone',
                    'data-rule-phoneOrEmailRequired' => 'true'
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => 'customer.add.email',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-lowercase',
                    'placeholder' => 'customer.add.email',
                    'data-rule-phoneOrEmailRequired' => 'true'
                ]
            ])
            ->add('apartment', ApartmentType::class)
        ;

        if (!$options['isMembership']) {
            $this->addNotes($builder);
        } else {
            $this->addNotifications($builder);
        }
    }

    /**
     * @param FormBuilderInterface $form
     */
    private function addNotes(FormBuilderInterface $form)
    {
        $form->add('notes', TextareaType::class, [
            'required' => false,
            'label' => 'customer.add.notes',
            'label_attr' => [
                'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
            ],
            'attr' => [
                'rows' => 7,
                'data-type' => 'string',
                'class' => 'form-control text-uppercase',
                'placeholder' => 'customer.add.notes'
            ]
        ]);
    }

    /**
     * @param FormBuilderInterface $form
     */
    public function addNotifications(FormBuilderInterface $form)
    {
        $form->add('notifications', CollectionType::class, [
            'entry_type' => NotificationType::class,
            'label' => false,
            'required' => false
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
            'translation_domain' => 'labels',
            'date_format' => 'yyyy-MM-dd',
            'isMembership' => null
        ]);
    }
}