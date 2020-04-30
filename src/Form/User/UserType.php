<?php

namespace App\Form\User;

use App\Entity\User\User;
use App\Form\Type\DateFormatType;
use App\Form\Type\LocaleType;
use App\Service\CountryList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class UserType extends AbstractType
{
    private $countryList;

    public function __construct(CountryList $countryList)
    {
        $this->countryList = $countryList;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale', LocaleType::class, [
                'label_attr' => [
                    'class' => 'col-md-2'
                ]
            ])
            ->add('dateFormat', DateFormatType::class, [
                'label_attr' => [
                    'class' => 'col-md-2'
                ]
            ])
            ->add('role', ChoiceType::class , [
                'choices'  => [
                    'account.users.owner' => 'owner',
                    'account.users.employee' => 'employee'
                ],
                'mapped' => false,
                'label' => 'account.users.role',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'class' => 'select',
                    'data-type' => 'string',
                    'data-empty' => 'false'
                ],
                'required' => false,
                'data' => $options['user_role'],
                'empty_data' => false,
                'placeholder' => false
            ])
            ->add('email', TextType::class, [
                'required' => false,
                'label' => 'account.users.email',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'placeholder' => 'account.users.email',
                    'data-empty' => 'false',
                    'class' => 'form-control'
                ]
            ])
            ->add('username', TextType::class, [
                'required' => false,
                'label' => 'account.users.username',
                'label_attr' => [
                    'class' => 'col-md-2 col-sm-3 col-xs-5 control-label'
                ],
                'attr' => [
                    'placeholder' => 'account.users.username',
                    'class' => 'form-control',
                    'maxlength' => 255,
                    'pattern' => '.{2,}',
                    'autofocus' => null,
                    'data-empty' => 'false',
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
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
            ])
            ->add('enabled', CheckboxType::class, [
                'required' => false,
                'label' => false,
                'attr' => [
                    'class' => 'switchery table-switch'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'labels',
            'user_role' => null,
            'allow_extra_fields' => true,
            'cascade_validation' => true
        ]);
    }

}
