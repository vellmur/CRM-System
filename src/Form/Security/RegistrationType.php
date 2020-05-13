<?php

namespace App\Form\Security;

use App\Entity\User\User;
use App\Form\Client\ClientNameType;
use App\Form\Type\LocaleType;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Class RegistrationType
 * @package App\Form\Security
 */
class RegistrationType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale', LocaleType::class , [
                'required' => false,
                'label' => 'register.your_language'
            ])
            ->add('username', null, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'register.username'
                ]
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'register.your_email'
                ]
            ])
            ->add('plainPassword', RepeatedType::class, [
                'required' => false,
                'type' => PasswordType::class,
                'invalid_message' => 'register.passwords_dont_match',
                'first_options' => [
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'register.create_password'
                    ]
                ],
                'second_options' => [
                    'attr' => [
                        'class' => 'form-control',
                        'placeholder' => 'register.repeat_password'
                    ]
                ]
            ])
            ->add('client', ClientNameType::class, [
                'mapped' => false,
                'validation_groups' => 'register_validation',
                'constraints' => [
                    new Valid()
                ]
            ])
            ->add('recaptcha', EWZRecaptchaType::class, [
                'attr' => [
                    'options' => [
                        'theme' => 'light',
                        'type'  => 'image',
                        'size'  => 'normal'
                    ]
                ],
                'mapped' => false,
                'constraints' => $_ENV['APP_ENV'] == 'prod' ?  [new IsTrue()] : []
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'messages',
            'validation_groups' => 'register_validation'
        ]);
    }
}