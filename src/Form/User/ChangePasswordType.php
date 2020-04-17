<?php

namespace App\Form\User;

use App\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;

class ChangePasswordType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('current_password', PasswordType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'account.users.prev_password',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'data-empty' => 'false',
                    'class' => 'form-control',
                    'placeholder' => '********'
                ],
                'constraints' => new UserPassword([
                    'message' => 'validation.form.wrong_password'
                ]),
            ])
            ->add('plainPassword', RepeatedType::class, [
                'translation_domain' => 'labels',
                'type' => PasswordType::class,
                'required' => false,
                'first_options'  => [
                    'label' => 'account.users.password',
                    'label_attr' => [
                        'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                    ],
                    'attr' => [
                        'data-empty' => 'false',
                        'class' => 'form-control',
                        'placeholder' => '********'
                    ]
                ],
                'second_options' => [
                    'label' => 'account.users.repeat_password',
                    'label_attr' => [
                        'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                    ],
                    'attr' => [
                        'data-empty' => 'false',
                        'class' => 'form-control',
                        'placeholder' => '********'
                    ]
                ],
                'invalid_message' => 'validation.form.password_mismatch'
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'labels'
        ]);
    }
}
