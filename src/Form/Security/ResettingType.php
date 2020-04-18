<?php

namespace App\Form\Security;

use App\Entity\User\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResettingType extends AbstractType
{
    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('plainPassword', RepeatedType::class, [
            'required' => false,
            'type' => PasswordType::class,
            'options' => [
                'attr' => [
                    'autocomplete' => 'new-password',
                ],
            ],
            'first_options' => [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'resetting.password',
                    'autofocus' => true
                ]
            ],
            'second_options' => [
                'required' => false,
                'attr' => [
                    'class' => 'form-control',
                    'placeholder' => 'resetting.repeat_password'
                ]
            ],
            'invalid_message' => $this->translator->trans('register.passwords_dont_match', [], 'messages')
        ]);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'messages'
        ]);
    }
}