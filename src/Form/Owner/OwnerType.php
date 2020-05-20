<?php

namespace App\Form\Owner;

use App\Entity\Owner\Owner;
use App\Form\Type\PhoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;

class OwnerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstname', TextType::class, [
                'label' => 'owner.add.firstname',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'owner.add.firstname'
                ],

            ])
            ->add('lastname', TextType::class, [
                'label' => 'owner.add.lastname',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'owner.add.lastname'
                ]
            ])
            ->add('phone', PhoneType::class, [
                'label' => 'owner.add.phone',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-uppercase',
                    'placeholder' => 'owner.add.phone',
                    'data-rule-phoneOrEmailRequired' => 'true'
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => 'owner.add.email',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'form-control text-lowercase',
                    'placeholder' => 'owner.add.email',
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
            'label' => 'owner.add.notes',
            'label_attr' => [
                'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
            ],
            'attr' => [
                'rows' => 7,
                'data-type' => 'string',
                'class' => 'form-control text-uppercase',
                'placeholder' => 'owner.add.notes'
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
            'data_class' => Owner::class,
            'translation_domain' => 'labels',
            'isMembership' => null
        ]);
    }
}