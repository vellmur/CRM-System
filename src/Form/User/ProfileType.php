<?php

namespace App\Form\User;

use App\Entity\User\User;
use App\Form\Building\BuildingType;
use App\Form\Subscriber\ProfileSubscriber;
use App\Form\Type\DateFormatType;
use App\Form\Type\LocaleType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Class UserFormType
 * @package App\Form\User
 */
class ProfileType extends AbstractType
{
    private $subscriber;

    public function __construct(ProfileSubscriber $subscriber)
    {
        $this->subscriber = $subscriber;
    }

    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale', LocaleType::class)
            ->add('dateFormat', DateFormatType::class)
            ->add('building', BuildingType::class, [
                'validation_groups' => 'profile_validation',
                'constraints' => [
                    new Valid()
                ]
            ]);

        $builder->addEventSubscriber($this->subscriber);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'translation_domain' => 'labels',
            'validation_groups' => 'profile_validation'
        ]);
    }
}
