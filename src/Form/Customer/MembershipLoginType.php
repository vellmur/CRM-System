<?php

namespace App\Form\Customer;

use App\Form\EventListener\MembershipLoginSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\NotBlank;

class MembershipLoginType extends AbstractType
{
    private $listener;

    public function __construct(MembershipLoginSubscriber $subscriber)
    {
        $this->listener = $subscriber;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', TextType::class, [
                'required' => false,
                'label' => 'Email',
                'label_attr' => [
                    'class' => 'control-label'
                ],
                'attr' => [
                    'data-empty' => 'false',
                    'class' => 'form-control',
                    'autofocus' => true
                ],
                'constraints' => [
                    new NotBlank(),
                ]
            ]);

        $builder->addEventSubscriber($this->listener);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'translation_domain' => 'labels'
        ]);
    }
}