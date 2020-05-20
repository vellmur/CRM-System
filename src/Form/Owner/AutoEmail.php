<?php

namespace App\Form\Owner;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class AutoEmail extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subject', TextType::class, [
                'required' => false,
                'label' => 'emails.subject',
                'label_attr' => [
                    'class' => 'col-md-1 control-label'
                ],
                'attr' => [
                    'data-type' => 'string',
                    'data-empty' => 'false',
                    'class' => 'form-control',
                    'placeholder' => 'emails.subject'
                ]
            ])
            ->add('text', TextareaType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'data-empty' => 'false'
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
            'data_class' => \App\Entity\Owner\Email\AutoEmail::class,
            'translation_domain' => 'labels'
        ]);
    }
}