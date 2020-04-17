<?php

namespace App\Form\Customer;

use App\Entity\Customer\Email\CustomerEmail;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class EmailType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [
                'required' => false,
                'label' => 'emails.subject',
                'label_attr' => [
                    'class' => 'col-md-2 control-label'
                ],
                'attr' => [
                    'data-empty' => 'false',
                    'class' => 'form-control',
                    'placeholder' => 'emails.subject'
                ]
            ])
            ->add('replyEmail', TextType::class, [
                'required' => false,
                'label' => 'emails.reply_to',
                'label_attr' => [
                    'class' => 'col-md-2 control-label'
                ],
                'attr' => [
                    'data-empty' => 'false',
                    'class' => 'form-control',
                    'placeholder' => 'emails.reply_to'
                ]
            ])
            ->add('replyName', TextType::class, [
                'required' => false,
                'label' => 'emails.reply_name',
                'label_attr' => [
                    'class' => 'col-md-2 control-label'
                ],
                'attr' => [
                    'data-empty' => 'false',
                    'class' => 'form-control',
                    'placeholder' => 'emails.reply_name'
                ]
            ])
            ->add('text', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'data-empty' => 'false'
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
            'data_class' => CustomerEmail::class,
            'allow_extra_fields' => true
        ]);
    }
}