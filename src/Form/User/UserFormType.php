<?php

namespace App\Form\User;

use App\Entity\User\User;
use App\Form\Client\ClientType;
use App\Form\EventListener\ProfileSubscriber;
use App\Form\Type\DateFormatType;
use App\Form\Type\LocaleType;
use App\Service\CountryList;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Valid;

/**
 * Class UserFormType
 * @package App\Form\User
 */
class UserFormType extends AbstractType
{
    private $subscriber;

    private $countryList;

    public function __construct(ProfileSubscriber $subscriber, CountryList $countryList)
    {
        $this->subscriber = $subscriber;
        $this->countryList = $countryList;
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
            ->add('client', ClientType::class, [
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
