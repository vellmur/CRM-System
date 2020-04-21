<?php

namespace App\Form\Security;

use App\Entity\Translation\TranslationLocale;
use App\Entity\User\User;
use App\Form\Client\ClientNameType;
use App\Repository\Translation\TranslationLocaleRepository;
use App\Service\CountryList;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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
            ->add('locale', ChoiceType::class , [
                'required' => false,
                'choices' => $options['locales'],
                'label' => 'register.your_language',
                'attr' => [
                    'class' => 'select'
                ],
                'placeholder' => 'register.your_language'
            ])
            ->add('client', ClientNameType::class, [
                'mapped' => false,
                'validation_groups' => 'register_validation',
                'constraints' => [
                    new Valid()
                ]
            ])
            ->add('recaptcha', EWZRecaptchaType::class, [
                'attr'  => [
                    'options' => [
                        'theme' => 'light',
                        'type'  => 'image',
                        'size'  => 'normal'
                    ]
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'messages',
            'validation_groups' => 'register_validation',
            'locales' => []
        ]);
    }
}